AI AGENT WORKFLOW PROMPTS
🔹 Prompt 1: Phase 1 - Initiation & Blueprint Generation
(Use this when you want to start a completely new feature/update)


Hello AI, I want to create a new update: [Insert your feature description here]. Based on our STRICT DEVELOPMENT PROTOCOL, analyze the current project structure and dependencies. Then, generate the Update Blueprint using TEMPLATE 1. Save this blueprint inside the _AI_Workflow/Updates_Docs/ directory with an appropriate ID and naming convention (e.g., UP-001_FeatureName.md). Once created, present it to me for review and wait for my approval before proceeding.



-----------------------------



🔹 Prompt 2: Phase 2 - Task Splitting & Documentation
(Use this after you read the Blueprint from Phase 1 and approve it)


Excellent, I approve this Blueprint. Now, execute Phase 2 of our protocol. Break down this Blueprint into strictly sequential, atomic tasks. Create a separate Markdown file for each task inside the _AI_Workflow/Tasks_Docs/ directory using TEMPLATE 2. Once all task files are generated, present the task list to me and wait for my explicit authorization to start executing Task 1.



-----------------------------

🔹 Prompt 3: Phase 3 - Execution & Reporting
(Use this to kick off the coding process for a specific task)

You are authorized to start executing [Task 1 / Task ID]. Write the complete, production-ready code. NO PLACEHOLDERS ARE ALLOWED.
Once you finish coding, do the following:
Provide a brief report of the changes made.
Automatically update the Master Checklist in the main Blueprint file inside Updates_Docs/.
Provide a suggested git commit command for this specific task.
Ask for my permission before renaming this task file to start with Finished_ and before proceeding to the next task.
