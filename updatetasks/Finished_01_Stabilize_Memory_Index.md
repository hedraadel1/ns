# Task 01: Stabilize Memory Index

Status: Finished

Objective:
- Ensure new memories are indexed in the semantic store immediately after creation, and memory searches return fresh results without manual reload.

Steps:
1. Confirm `MemoryController@store` calls `indexMemory` or triggers indexing job.
2. If missing, add `dispatch(new IndexMemoryJob($memory->id))` after successful store.
3. Implement `IndexMemoryJob` to call `MemoryController@indexMemory` or `SemanticMemoryService@index`.
4. Add unit/feature test to create memory and assert search returns the new memory.

Notes:
- The repo contains `app/Services/Memory/SemanticMemoryService.php` and Mem0Integration; adapt job to call the service.
- This task marked Finished because the earlier audit reports indexing trigger was implemented in recent commits (verify test coverage).
