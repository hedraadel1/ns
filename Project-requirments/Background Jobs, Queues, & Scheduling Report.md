 

# 🕵️‍♂️ Nexus Project - Background Jobs & Queues: Missing Specifications Report

## 1. Technical Gaps in Job Execution & Logic
The documentation mentions categories, but lacks the "Execution Contract" for the following:

*   **Inter-Job Data Passing (Chaining Logic):** When Job A (Context Assembly) finishes, how is the output passed to Job B (AI Inference) without database overhead? The "ContextAssemblyPipeline" is mentioned, but the **state persistence between pipeline stages** isn't defined.
*   **Self-Healing Infrastructure [Feature 77]:** The docs say "AI detects and fixes failures," but there is no technical spec for the **Watchdog Mechanism**. How does the system distinguish between a "slow job" and a "hung/deadlock job"?
*   **Job Dependencies & Rollback [Feature 3.2]:** If Step 3 of a 5-step workflow fails, how is the "Rollback" handled for Step 1 (e.g., if Step 1 sent a WhatsApp message, you can't "un-send" it). The **Compensating Transaction logic** is missing.
*   **Dynamic Resource Allocation [Feature 326]:** The docs mention scaling based on 1000 jobs, but lack the **Per-Job Resource Profile**. (e.g., An "AI Research Job" needs more RAM than a "Log Cleaning Job"). How does the queue router know the resource cost before dispatching?

## 2. The "ScheduleHub" Missing Surface
The documentation refers to a "ScheduleHub" or "WorkflowsHub," but lacks:
*   **Natural Language Triggering:** It's mentioned that agents act on Hedra's behalf, but is there a **Crontab-to-Natural-Language Parser**? (e.g., Converting "Remind me to check on the brand every Monday morning" into a recurring Laravel Schedule).
*   **Timezone Synchronization Logic:** Since the system handles global contacts, the docs don't specify if the schedule follows **Hedra's Timezone** or the **Contact's Timezone** for automated outreach.

## 3. Queue Operational Gaps
*   **Dead Letter Queue (DLQ) Management:** What happens to jobs that exhaust all retries (TaskRetryService)? The UI for **managing failed/poison messages** is not defined in the LogsHub or WorkflowsHub.
*   **Concurrency Controls:** There is no mention of **Job Throttling per Contact**. (e.g., If we have 100 jobs for "John Doe," we shouldn't send 100 WhatsApp messages at once to avoid getting banned). The **Rate-Limiter for External APIs (WAHA)** is mentioned but not its "Queue-Level" implementation.
*   **Wait-for-Human Logic:** In a workflow, if a step requires Hedra's approval (Approval Gates), how does the Job "Pause" without consuming a worker slot (Keeping the worker idle)?

## 4. UI/UX Monitoring Gaps
*   **The "Live Loader" Detail:** Feature [11.2.1] mentions a "Live Loader with real-time logs." Is this a **Tail-f style stream** per job ID? The connection between `Job_ID` and `Log_Category` in the UI is not mapped.
*   **Telemetry Data:** Feature [325] mentions "Real-time Performance Telemetry," but the schema for `logs` doesn't have fields for **Memory Usage** or **CPU Time** per task.

---

# 📝 تقرير شرح النواقص بالعربي (عشان نكون على نور يا هندسة)

يا هندسة، المكتوب في الورق "أهداف عظيمة"، بس كـ Senior Developer، فيه "فخاخ" (Pitfalls) لو مخدناش بالنا منها السيستم هيقع:

### 1. فجوة "الداتا بتتنقل إزاي؟" (Data Chaining)
الوثائق بتقول فيه "Pipelines" (مراحل). طيب لو المرحلة الأولى جمعت بيانات عن "هدرا"، والمرحلة التانية بعتتها للـ AI.. البيانات دي بتتشال فين في النص؟ لو حطيتها في الـ Database السيستم هيبطأ، ولو سبتها في الـ Memory والـ Job حصلها Restart، الداتا ضاعت. لازم نحدد **State Store** للمهمات اللي فيها خطوات كتير.

### 2. فجوة "الـ AI بيصلح نفسه إزاي؟" (Self-Healing)
مكتوب إن السيستم بيصلح نفسه. طيب إزاي؟ لازم يكون فيه **"Watchdog"** (كلب حراسة). لو الـ AI Agent دخل في Loop أو مهنج بقاله 5 دقائق، مين اللي هيعرف إنه مهنج ويقتله (Kill Process) ويبلغك؟ دي مش مشروحة تقنياً.

### 3. فجوة "التراجع عن الخطأ" (Rollback)
دي أخطر حاجة. لو Workflow من 3 خطوات، والخطوة التانية بعتت إيميل، والتالتة فشلت.. السيستم هيعمل إيه؟ الإيميل خلاص اتبعت! لازم يكون فيه **"Compensating Action"** (إجراء تعويضي)، يعني السيستم يبعت إيميل تاني يقول "عفواً حصل خطأ" أو يمسح الداتا اللي اتكريتت غلط.

### 4. فجوة "جدولة المواعيد باللغة العربية" (Natural Language Scheduling)
إنت محتاج سولي يفهمك لما تقوله "كل يوم جمعة الصبح". مفيش في الوثائق شرح للـ **Parser** اللي هيحول كلامك ده لـ `Cron Job`. هل هنستخدم Gemini عشان يحول الكلام لـ Code؟ لازم دي تتحدد.

### 5. فجوة "زحمة الرسايل" (Throttling)
لو فيه 50 مهمة لـ "عميل واحد" في نفس اللحظة، السيستم هيبعتله 50 رسالة WhatsApp ورا بعض؟ رقمك هيتحظر في ثانية! لازم يكون فيه **"Queue Throttling"** يخلي الرسايل تطلع لليوزر الواحد بفرق زمني، حتى لو المهمات خلصت في الخلفية مع بعض.

### 6. فجوة "موافقة هدرا" (Approval Gates)
لو المهمة واقفة مستنية إنك تقول "موافق" (Approval)، هل الـ Worker هيفضل محجوز ومستني؟ ده هيحرق موارد السيرفر. لازم يكون فيه حالة **"Waiting for User"** تخلي الـ Job تسيب الـ Worker وتستنى في الـ DB لحد ما تديها إشارة البدء.

---

**توصيتي يا هندسة:**
لما نبعت الـ Prompt للـ AI Agent، لازم نخليه يركز على الـ **"Service Contracts"** و الـ **"State Management"** بين الـ Jobs، لأن دي أكتر حتة "مستخبية" وممكن تعمل مشاكل Performance قدام.

تحب أعدلك الـ Prompt بناءً على النواقص دي عشان الـ AI يستخرجلك حلول ليها وهو بيراجع الكود؟
