<?php
namespace App\Events;
use App\Models\Contact;
class ContactCreated extends Event
{
    public function __construct(public Contact $contact, public array $metadata = [])
    { parent::__construct(); }
}
