<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\ContactHubService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function __construct(protected ContactHubService $contactHubService)
    {
    }

    public function index(Request $request)
    {
        $query = Contact::query();

        if ($request->filled('search')) {
            $query->search($request->query('search'));
        }

        if ($request->filled('type')) {
            $query->ofType($request->query('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $contacts = $query->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return response()->json(['data' => $contacts]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'type' => ['nullable', Rule::in(Contact::getAvailableTypes())],
            'title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
            'metadata' => ['nullable', 'array'],
            'attributes' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        $contact = Contact::create(array_merge($data, ['uuid' => Contact::generateUuid(), 'type' => $data['type'] ?? Contact::TYPE_CONTACT]));
        $this->contactHubService->syncContactDetails($contact);

        return response()->json(['data' => $contact], 201);
    }

    public function show($id)
    {
        $contact = Contact::with(['conversations', 'notes', 'tags', 'rules', 'customFields', 'memories'])
            ->findOrFail($id);

        return response()->json(['data' => $contact]);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:32'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'type' => ['nullable', Rule::in(Contact::getAvailableTypes())],
            'title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
            'metadata' => ['nullable', 'array'],
            'attributes' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        $contact->update($data);
        $this->contactHubService->syncContactDetails($contact);

        return response()->json(['data' => $contact]);
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->json(['message' => 'contact deleted', 'id' => $id]);
    }

    public function getMemory($id)
    {
        $contact = Contact::with('memories')->findOrFail($id);

        return response()->json(['data' => ['contact_id' => $id, 'memories' => $contact->memories]]);
    }

    public function getRules($id)
    {
        $contact = Contact::with('rules')->findOrFail($id);

        return response()->json(['data' => ['contact_id' => $id, 'rules' => $contact->rules]]);
    }

    public function getAnalytics($id)
    {
        $contact = Contact::findOrFail($id);
        $analytics = $this->contactHubService->getContactAnalytics($contact);

        return response()->json(['data' => ['contact_id' => $id, 'analytics' => $analytics]]);
    }

    public function import(Request $request)
    {
        $payload = [];

        if ($request->has('contacts') && is_array($request->input('contacts'))) {
            $payload = $request->input('contacts');
        } elseif ($request->hasFile('file') && $request->file('file')->isValid()) {
            $payload = $this->parseCsv($request->file('file')->getRealPath());
        } else {
            abort(422, 'Provide a contacts array or an uploaded CSV file.');
        }

        $created = 0;
        foreach ($payload as $row) {
            $data = $this->normalizeImportRow($row);
            $contact = Contact::create(array_merge($data, ['uuid' => Contact::generateUuid(), 'type' => $data['type'] ?? Contact::TYPE_CONTACT]));
            $this->contactHubService->syncContactDetails($contact);
            $created++;
        }

        return response()->json(['message' => 'Contacts imported successfully', 'created' => $created]);
    }

    public function export(Request $request)
    {
        $contacts = Contact::orderBy('name')->get();
        $rows = [];
        $rows[] = ['uuid', 'name', 'email', 'phone', 'type', 'title', 'company', 'avatar_url', 'is_active', 'last_seen_at'];

        foreach ($contacts as $contact) {
            $rows[] = [
                $contact->uuid,
                $contact->name,
                $contact->email,
                $contact->phone,
                $contact->type,
                $contact->title,
                $contact->company,
                $contact->avatar_url,
                $contact->is_active ? '1' : '0',
                optional($contact->last_seen_at)->toDateTimeString(),
            ];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($item) => '"' . str_replace('"', '""', (string) ($item ?? '')) . '"', $row)) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="contacts.csv"',
        ]);
    }

    protected function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $header = null;

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if ($header === null) {
                $header = array_map('trim', $data);
                continue;
            }

            if (count($data) !== count($header)) {
                continue;
            }

            $rows[] = array_combine($header, $data);
        }

        fclose($handle);

        return $rows;
    }

    protected function normalizeImportRow(array $row): array
    {
        return [
            'name' => $row['name'] ?? $row['full_name'] ?? $row['contact_name'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'type' => $row['type'] ?? null,
            'title' => $row['title'] ?? null,
            'company' => $row['company'] ?? null,
            'avatar_url' => $row['avatar_url'] ?? null,
            'metadata' => isset($row['metadata']) ? json_decode($row['metadata'], true) : null,
            'attributes' => isset($row['attributes']) ? json_decode($row['attributes'], true) : null,
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            'last_seen_at' => $row['last_seen_at'] ?? null,
        ];
    }
}
