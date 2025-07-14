<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Attendence;
use App\Models\Payroll;
use App\Models\Department;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ResponseFormatter
{
    public static function formatEmployeeCount($count)
    {
        return "<div class='result-card'>" .
               "<h3>إجمالي عدد الموظفين</h3>" .
               "<div class='main-number'>{$count}</div>" .
               "<p class='sub-text'>موظف مسجل في النظام</p>" .
               "</div>";
    }

    public static function formatEmployeeList($employees)
    {
        if (empty($employees)) {
            return "<div class='info-message'>لا توجد بيانات موظفين متاحة حالياً</div>";
        }

        $response = "<div class='results-container'>";
        $response .= "<div class='results-header'>";
        $response .= "<h3>قائمة الموظفين</h3>";
        $response .= "<span class='count-badge'>" . count($employees) . " موظف</span>";
        $response .= "</div>";
        
        foreach ($employees as $index => $employee) {
            $response .= "<div class='employee-card'>";
            $response .= "<div class='employee-header'>";
            $response .= "<span class='employee-name'>" . $employee->first_name . " " . $employee->last_name . "</span>";
            $response .= "<span class='employee-number'>#" . str_pad($employee->id, 3, '0', STR_PAD_LEFT) . "</span>";
            $response .= "</div>";
            $response .= "<div class='employee-details'>";
            $response .= "<div class='detail-item'>";
            $response .= "<span class='label'>القسم</span>";
            $response .= "<span class='value'>" . ($employee->department->dept_name ?? 'غير محدد') . "</span>";
            $response .= "</div>";
            $response .= "<div class='detail-item'>";
            $response .= "<span class='label'>الراتب</span>";
            $response .= "<span class='value salary'>" . number_format($employee->salary ?? 0) . " جنيه</span>";
            $response .= "</div>";
            $response .= "</div>";
            $response .= "</div>";
        }
        
        $response .= "</div>";
        return $response;
    }

    public static function formatAttendanceToday($attendanceData)
    {
        $response = "<div class='attendance-summary'>";
        $response .= "<h3>تقرير الحضور اليوم</h3>";
        
        if (isset($attendanceData['total'])) {
            $response .= "<div class='stats-grid'>";
            
            $response .= "<div class='stat-item present'>";
            $response .= "<div class='stat-number'>" . ($attendanceData['present'] ?? 0) . "</div>";
            $response .= "<div class='stat-label'>حاضر</div>";
            $response .= "</div>";
            
            $response .= "<div class='stat-item late'>";
            $response .= "<div class='stat-number'>" . ($attendanceData['late'] ?? 0) . "</div>";
            $response .= "<div class='stat-label'>متأخر</div>";
            $response .= "</div>";
            
            $response .= "<div class='stat-item absent'>";
            $response .= "<div class='stat-number'>" . ($attendanceData['absent'] ?? 0) . "</div>";
            $response .= "<div class='stat-label'>غائب</div>";
            $response .= "</div>";
            
            $response .= "</div>";
            
            // إضافة نسبة الحضور
            $total = ($attendanceData['present'] ?? 0) + ($attendanceData['late'] ?? 0) + ($attendanceData['absent'] ?? 0);
            if ($total > 0) {
                $attendanceRate = round((($attendanceData['present'] ?? 0) / $total) * 100, 1);
                $response .= "<div class='attendance-rate'>";
                $response .= "<span class='rate-label'>معدل الحضور</span>";
                $response .= "<span class='rate-value'>{$attendanceRate}%</span>";
                $response .= "</div>";
            }
        }
        
        $response .= "</div>";
        return $response;
    }

    public static function formatSalaryInfo($salaries)
    {
        if (empty($salaries)) {
            return "<div class='info-message'>لا توجد بيانات رواتب متاحة</div>";
        }

        $total = array_sum(array_column($salaries, 'salary'));
        $average = $total / count($salaries);
        $highest = max(array_column($salaries, 'salary'));
        $lowest = min(array_column($salaries, 'salary'));

        $response = "<div class='salary-analysis'>";
        $response .= "<h3>تحليل الرواتب</h3>";
        
        $response .= "<div class='analysis-grid'>";
        
        $response .= "<div class='analysis-item total'>";
        $response .= "<div class='analysis-number'>" . number_format($total) . "</div>";
        $response .= "<div class='analysis-label'>إجمالي الرواتب</div>";
        $response .= "<div class='analysis-unit'>جنيه</div>";
        $response .= "</div>";
        
        $response .= "<div class='analysis-item average'>";
        $response .= "<div class='analysis-number'>" . number_format($average) . "</div>";
        $response .= "<div class='analysis-label'>متوسط الراتب</div>";
        $response .= "<div class='analysis-unit'>جنيه</div>";
        $response .= "</div>";
        
        $response .= "<div class='analysis-item highest'>";
        $response .= "<div class='analysis-number'>" . number_format($highest) . "</div>";
        $response .= "<div class='analysis-label'>أعلى راتب</div>";
        $response .= "<div class='analysis-unit'>جنيه</div>";
        $response .= "</div>";
        
        $response .= "<div class='analysis-item lowest'>";
        $response .= "<div class='analysis-number'>" . number_format($lowest) . "</div>";
        $response .= "<div class='analysis-label'>أقل راتب</div>";
        $response .= "<div class='analysis-unit'>جنيه</div>";
        $response .= "</div>";
        
        $response .= "</div>";
        $response .= "</div>";
        
        return $response;
    }

    public static function formatDepartmentStats($departments)
    {
        if (empty($departments)) {
            return "<div class='info-message'>لا توجد أقسام متاحة</div>";
        }

        $response = "<div class='departments-overview'>";
        $response .= "<h3>نظرة عامة على الأقسام</h3>";
        
        $totalEmployees = array_sum(array_column($departments, 'employee_count'));
        
        foreach ($departments as $dept) {
            $percentage = $totalEmployees > 0 ? round(($dept['employee_count'] / $totalEmployees) * 100, 1) : 0;
            
            $response .= "<div class='department-item'>";
            $response .= "<div class='department-header'>";
            $response .= "<span class='department-name'>" . $dept['name'] . "</span>";
            $response .= "<span class='department-count'>" . $dept['employee_count'] . " موظف</span>";
            $response .= "</div>";
            $response .= "<div class='department-bar'>";
            $response .= "<div class='bar-fill' style='width: {$percentage}%'></div>";
            $response .= "</div>";
            $response .= "<div class='department-percentage'>{$percentage}% من إجمالي الموظفين</div>";
            $response .= "</div>";
        }
        
        $response .= "</div>";
        return $response;
    }

    public static function formatGeneralResponse($message)
    {
        // تنظيف شامل من أي رموز أو أيقونات
        $message = preg_replace('/[📊📋👤💰⭐✅❌🔍💡👥📈📉🏢⏰➕🏛️💼💵🌍🆔🎂📱📧🏠📅🕐🕕🎯📝📞⚠️🚀🔧]/u', '', $message);
        
        // إزالة أي رموز unicode للأيقونات
        $message = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $message);
        $message = preg_replace('/[\x{2600}-\x{27BF}]/u', '', $message);
        
        // إزالة النجوم وعلامات التنسيق
        $message = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $message);
        $message = str_replace(['**', '*', '⭐', '✅', '❌', '📊'], '', $message);
        
        // إزالة عبارات معينة
        $message = preg_replace('/النتائج:\s*/u', '', $message);
        $message = preg_replace('/تحليل سريع:\s*/u', '', $message);
        $message = preg_replace('/النتيجة:\s*/u', '', $message);
        
        // تنظيف المسافات
        $message = trim(preg_replace('/\s+/', ' ', $message));
        
        // إذا كان النص فارغ بعد التنظيف، أضف رد افتراضي
        if (empty(trim($message))) {
            $message = "تم معالجة طلبك بنجاح";
        }
        
        // تحسين التنسيق
        $message = "<div class='general-response'>" . $message . "</div>";
        
        return $message;
    }
}

class HRChatService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;
    protected $model;
    protected $maxTokens;
    protected $temperature;

    public function __construct()
    {
        // قراءة الإعدادات من config/services.php
        $config = config('services.openrouter');
        
        $this->apiKey = $config['api_key'];
        $this->apiUrl = $config['api_url'];
        $this->model = $config['model'];
        $this->maxTokens = $config['max_tokens'];
        $this->temperature = $config['temperature'];

        $this->client = new Client([
            'timeout' => $config['timeout'],
            'connect_timeout' => 30,
            'verify' => false,
        ]);

        // تحقق من وجود API Key
        if (!$this->apiKey) {
            throw new \Exception('OPENROUTER_API_KEY غير موجود في ملف .env');
        }

        Log::info('HRChatService initialized', [
            'api_url' => $this->apiUrl,
            'model' => $this->model,
            'has_api_key' => !empty($this->apiKey)
        ]);
    }

    public function chat($message, $conversationHistory = [])
    {
        try {
            Log::info('HR Chat Request Started', [
                'message_preview' => substr($message, 0, 100),
                'history_count' => count($conversationHistory),
                'timestamp' => now()
            ]);

            $systemPrompt = $this->buildHRSystemPrompt();
            
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ...$conversationHistory,
                ['role' => 'user', 'content' => $message]
            ];

            Log::info('Sending request to OpenRouter', [
                'url' => $this->apiUrl . '/chat/completions',
                'model' => $this->model,
                'messages_count' => count($messages)
            ]);

            $response = $this->client->post($this->apiUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name', 'HR Management System')
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                    'stream' => false
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            Log::info('OpenRouter Response Received', [
                'status_code' => $statusCode,
                'has_choices' => isset($responseBody['choices']),
                'choices_count' => isset($responseBody['choices']) ? count($responseBody['choices']) : 0,
                'usage' => $responseBody['usage'] ?? null
            ]);

            if ($statusCode !== 200) {
                throw new \Exception("HTTP Error: $statusCode - " . ($responseBody['error']['message'] ?? 'Unknown error'));
            }

            if (!isset($responseBody['choices'][0]['message']['content'])) {
                Log::error('Invalid OpenRouter response format', ['response' => $responseBody]);
                throw new \Exception('Invalid response format from OpenRouter API');
            }

            $aiResponse = $responseBody['choices'][0]['message']['content'];
            
            Log::info('AI Response Generated', [
                'response_length' => strlen($aiResponse),
                'contains_sql' => $this->containsSQLRequest($aiResponse)
            ]);

            // تحقق إذا كان الـ AI يريد تنفيذ استعلام
            if ($this->containsSQLRequest($aiResponse)) {
                $queryResult = $this->executeHRQuery($aiResponse);
                $finalResponse = $this->formatResponseWithData($aiResponse, $queryResult);
                
                Log::info('SQL Query Executed', [
                    'has_data' => isset($queryResult['data']),
                    'has_error' => isset($queryResult['error'])
                ]);
                
                return $finalResponse;
            }

            // تنظيف نهائي للاستجابة حتى لو لم تحتوي على SQL
            $cleanAiResponse = $this->finalCleanup($aiResponse);
            return ResponseFormatter::formatGeneralResponse($cleanAiResponse);
            
        } catch (RequestException $e) {
            $errorMsg = 'Network Error: ' . $e->getMessage();
            $errorDetails = [];
            
            if ($e->hasResponse()) {
                $errorResponse = json_decode($e->getResponse()->getBody(), true);
                $errorMsg .= ' - ' . ($errorResponse['error']['message'] ?? 'Unknown API error');
                $errorDetails = $errorResponse;
            }
            
            Log::error('HR Chat Network Error', [
                'error' => $errorMsg,
                'code' => $e->getCode(),
                'details' => $errorDetails
            ]);
            
            return "<div class='info-message'>عذراً، حدث خطأ في الاتصال بخدمة الذكاء الاصطناعي. يرجى المحاولة مرة أخرى خلال دقائق.</div>";
            
        } catch (\Exception $e) {
            Log::error('HR Chat General Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // في حالة فشل الـ AI، اعطي إجابة بسيطة مبنية على keywords
            return $this->getFallbackResponse($message);
        }
    }

    protected function buildHRSystemPrompt()
    {
        try {
            $hrSchema = $this->getHRDatabaseSchema();
            $businessContext = $this->getBusinessContext();
            
            return "أنت مساعد ذكي متخصص في نظام إدارة الموارد البشرية. تتحدث باللغة العربية وتجيب بدقة عالية.

هيكل قاعدة البيانات (الحقول الموجودة فعلياً):
{$hrSchema}

السياق التجاري:
{$businessContext}

قواعد مهمة جداً - يجب اتباعها بدقة:
- لا تُظهر للمستخدم أي SQL code مطلقاً
- لا تستخدم أي أيقونات أو رموز تعبيرية أو نجوم في ردودك (ممنوع: 📊 📋 👤 💰 ⭐ ✅ ❌ 🔍 💡 👥 📈 📉 🏢 ⏰ ➕ ** *)
- لا تستخدم ** للتأكيد أو التنسيق
- اكتب بأسلوب طبيعي وبسيط بدون أي رموز
- استخدم فقط الحقول الموجودة فعلياً في قاعدة البيانات
- لا تخترع أسماء حقول غير موجودة
- عند الحاجة لاستعلام، ضع SQL داخل <SQL></SQL> فقط
- استخدم SELECT فقط - لا UPDATE/DELETE/INSERT

الحقول المتاحة بالضبط:
- employees: id, first_name, last_name, email, phone, address, salary, hire_date, department_id, working_hours_per_day, salary_per_hour, gender, nationality, national_id, birthdate, default_check_in_time, default_check_out_time
- departments: id, dept_name
- attendances: id, employee_id, date, checkInTime, checkOutTime, lateDurationInHours, overtimeDurationInHours, status
- payrolls: id, employee_id, month, month_days, attended_days, absent_days, total_overtime, total_deduction, net_salary, salary_per_hour

أمثلة على استعلامات صحيحة:

للموظفين حسب الأقسام:
<SQL>SELECT d.dept_name, e.first_name, e.last_name, e.salary FROM departments d LEFT JOIN employees e ON d.id = e.department_id ORDER BY d.dept_name, e.first_name</SQL>

لعدد الموظفين:
<SQL>SELECT COUNT(*) as total FROM employees</SQL>

للحضور اليوم:
<SQL>SELECT e.first_name, e.last_name, a.checkInTime, a.status FROM employees e LEFT JOIN attendances a ON e.id = a.employee_id WHERE DATE(a.date) = CURDATE()</SQL>

أسلوب الرد المطلوب:
- اكتب بأسلوب طبيعي وبسيط
- لا تستخدم أي رموز أو أيقونات أو نجوم
- ابدأ بعبارة ودية مثل: دعني أتحقق من ذلك
- كن دقيقاً ومتخصصاً في الموارد البشرية
- اعرض النتائج بتنسيق جميل ومنظم
- اشرح المعلومات بطريقة مفيدة ومهنية
- أضف تعليقات وملاحظات مفيدة حول البيانات
- لا تذكر أي تفاصيل تقنية للمستخدم
- تجنب استخدام أي رموز تماماً في الرد

مثال على رد صحيح:
دعني أتحقق من عدد الموظفين في النظام.
<SQL>SELECT COUNT(*) as total FROM employees</SQL>

مثال على رد خاطئ (لا تفعل هذا):
📊 النتائج: ⭐ 5 موظفين ⭐
✅ النتيجة: 1000

بدلاً من ذلك، اكتب:
دعني أتحقق من عدد الموظفين في النظام.
<SQL>SELECT COUNT(*) as total FROM employees</SQL>";

        } catch (\Exception $e) {
            Log::error('Error building system prompt', ['error' => $e->getMessage()]);
            return "أنت مساعد ذكي متخصص في نظام إدارة الموارد البشرية. لا تستخدم أي أيقونات أو رموز في ردودك. اكتب بأسلوب طبيعي وبسيط.";
        }
    }

    protected function getHRDatabaseSchema()
    {
        return "
employees (جدول الموظفين - الحقول المتاحة):
- id: الرقم التعريفي
- first_name: الاسم الأول  
- last_name: اسم العائلة
- email: البريد الإلكتروني
- phone: رقم الهاتف
- address: العنوان
- salary: الراتب الشهري
- hire_date: تاريخ التوظيف
- department_id: رقم القسم (foreign key للربط مع departments)
- working_hours_per_day: ساعات العمل اليومية
- salary_per_hour: الراتب بالساعة
- gender: الجنس
- nationality: الجنسية  
- national_id: رقم الهوية
- birthdate: تاريخ الميلاد
- default_check_in_time: وقت الحضور الافتراضي
- default_check_out_time: وقت الانصراف الافتراضي

departments (جدول الأقسام - الحقول المتاحة):
- id: الرقم التعريفي للقسم
- dept_name: اسم القسم

attendances (جدول الحضور - الحقول المتاحة):
- id: الرقم التعريفي
- employee_id: رقم الموظف (foreign key للربط مع employees)
- date: تاريخ الحضور
- checkInTime: وقت الحضور الفعلي
- checkOutTime: وقت الانصراف الفعلي
- lateDurationInHours: ساعات التأخير
- overtimeDurationInHours: ساعات العمل الإضافية
- status: حالة الحضور (present, absent, late, etc.)

payrolls (جدول كشوف المرتبات - الحقول المتاحة):
- id: الرقم التعريفي
- employee_id: رقم الموظف (foreign key للربط مع employees)
- month: الشهر
- month_days: عدد أيام الشهر
- attended_days: عدد أيام الحضور
- absent_days: عدد أيام الغياب
- total_overtime: إجمالي الساعات الإضافية
- total_deduction: إجمالي الخصومات
- total_deduction_amount: مبلغ الخصومات
- late_deduction_amount: خصومات التأخير
- absence_deduction_amount: خصومات الغياب
- total_bonus_amount: إجمالي المكافآت
- net_salary: صافي الراتب
- salary_per_hour: الراتب بالساعة

حقول غير موجودة (لا تستخدمها):
- position, job_title, role في جدول employees
- department_name في أي جدول
- employee_name في أي جدول
- أي حقول أخرى غير مذكورة أعلاه";
    }

    protected function getBusinessContext()
    {
        try {
            $stats = Cache::remember('hr_basic_stats', 300, function() {
                return [
                    'total_employees' => Employee::count(),
                    'total_departments' => Department::count(),
                    'today_attendances' => Attendence::whereDate('date', today())->count(),
                    'this_month_payrolls' => Payroll::whereMonth('created_at', now()->month)->count(),
                ];
            });

            return "
إحصائيات سريعة:
- إجمالي الموظفين: {$stats['total_employees']}
- عدد الأقسام: {$stats['total_departments']}
- حضور اليوم: {$stats['today_attendances']}
- كشوف مرتبات هذا الشهر: {$stats['this_month_payrolls']}

التاريخ الحالي: " . now()->format('Y-m-d H:i:s') . "
الشهر الحالي: " . now()->format('F Y');

        } catch (\Exception $e) {
            return "Business context temporarily unavailable.";
        }
    }

    protected function containsSQLRequest($response)
    {
        return preg_match('/<SQL>(.*?)<\/SQL>/s', $response);
    }

    protected function executeHRQuery($response)
    {
        if (preg_match('/<SQL>(.*?)<\/SQL>/s', $response, $matches)) {
            $sql = trim($matches[1]);
            
            Log::info('Executing SQL Query', ['sql' => $sql]);
            
            // أمان: السماح بـ SELECT فقط
            if (!preg_match('/^\s*SELECT\s+/i', $sql)) {
                return ['error' => 'يُسمح باستعلامات SELECT فقط من أجل الأمان'];
            }
            
            try {
                $result = DB::select($sql);
                Log::info('SQL Query Success', ['result_count' => count($result)]);
                return ['data' => $result, 'query' => $sql];
            } catch (\Exception $e) {
                Log::error('SQL Query Error', [
                    'error' => $e->getMessage(),
                    'sql' => $sql
                ]);
                return ['error' => 'خطأ في تنفيذ الاستعلام: ' . $e->getMessage()];
            }
        }
        
        return null;
    }

    protected function formatResponseWithData($response, $queryResult)
    {
        // إزالة أي SQL code من الاستجابة
        $cleanResponse = preg_replace('/<SQL>.*?<\/SQL>/s', '', $response);
        
        // إزالة أي أيقونات أو رموز غير مرغوب فيها بشكل شامل
        $cleanResponse = preg_replace('/[📊📋👤💰⭐✅❌🔍💡👥📈📉🏢⏰➕🏛️💼💵🌍🆔🎂📱📧🏠📅🕐🕕🎯📝📞⚠️🚀🔧]/u', '', $cleanResponse);
        
        // إزالة النجوم والرموز الخاصة
        $cleanResponse = preg_replace('/\*\*(.*?)\*\*/', '$1', $cleanResponse);
        $cleanResponse = str_replace(['**', '*'], '', $cleanResponse);
        
        // إزالة أي نمط من الأيقونات المحتملة
        $cleanResponse = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $cleanResponse);
        $cleanResponse = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanResponse);
        $cleanResponse = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanResponse);
        
        // إزالة عبارات مثل "النتائج:" و "تحليل سريع:"
        $cleanResponse = preg_replace('/النتائج:\s*/u', '', $cleanResponse);
        $cleanResponse = preg_replace('/تحليل سريع:\s*/u', '', $cleanResponse);
        $cleanResponse = preg_replace('/النتيجة:\s*/u', '', $cleanResponse);
        
        // إزالة أي أكواد SQL متبقية
        $cleanResponse = preg_replace('/```sql.*?```/s', '', $cleanResponse);
        $cleanResponse = preg_replace('/SELECT.*?;/si', '', $cleanResponse);
        
        // تنظيف النص من المسافات الزائدة
        $cleanResponse = trim(preg_replace('/\s+/', ' ', $cleanResponse));
        $cleanResponse = preg_replace('/\n\s*\n/', '\n', $cleanResponse);
        
        if (isset($queryResult['error'])) {
            return $this->handleQueryError($queryResult['error'], $cleanResponse);
        }
        
        if (isset($queryResult['data'])) {
            return $this->formatDataResponse($queryResult['data'], $cleanResponse);
        }
        
        // تنظيف نهائي للرد العام
        $cleanResponse = $this->finalCleanup($cleanResponse);
        
        return ResponseFormatter::formatGeneralResponse($cleanResponse ?: "تم معالجة طلبك بنجاح");
    }

    protected function finalCleanup($text)
    {
        // تنظيف نهائي شامل
        $text = preg_replace('/[📊📋👤💰⭐✅❌🔍💡👥📈📉🏢⏰➕🏛️💼💵🌍🆔🎂📱📧🏠📅🕐🕕🎯📝📞⚠️🚀🔧]/u', '', $text);
        
        // إزالة أي رموز unicode للأيقونات
        $text = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $text);
        $text = preg_replace('/[\x{2600}-\x{27BF}]/u', '', $text);
        
        // إزالة النجوم وعلامات التنسيق
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = str_replace(['**', '*', '⭐', '✅', '❌', '📊'], '', $text);
        
        // تنظيف المسافات
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        return $text;
    }

    protected function formatDataResponse($data, $context)
    {
        if (empty($data)) {
            return "<div class='info-message'>لا توجد نتائج متطابقة مع طلبك</div>";
        }
        
        // تحديد نوع البيانات والتنسيق المناسب
        $firstRow = (array)$data[0];
        
        // عدد الموظفين
        if (count($data) === 1 && isset($firstRow['total'])) {
            return ResponseFormatter::formatEmployeeCount($firstRow['total']);
        }
        
        // إذا كانت النتيجة رقم واحد فقط
        if (count($data) === 1 && count($firstRow) === 1) {
            $value = array_values($firstRow)[0];
            $label = $this->getContextLabel($context);
            return "<div class='result-card'>" .
                   "<h3>{$label}</h3>" .
                   "<div class='main-number'>" . number_format($value) . "</div>" .
                   "</div>";
        }
        
        // قائمة الموظفين
        if (isset($firstRow['first_name']) || isset($firstRow['last_name'])) {
            return $this->formatEmployeeResults($data);
        }
        
        // بيانات الحضور
        if (isset($firstRow['status']) || isset($firstRow['checkInTime'])) {
            return $this->formatAttendanceResults($data);
        }
        
        // بيانات الرواتب
        if (isset($firstRow['salary']) || isset($firstRow['net_salary'])) {
            return $this->formatSalaryResults($data);
        }
        
        // بيانات الأقسام
        if (isset($firstRow['dept_name'])) {
            return $this->formatDepartmentResults($data);
        }
        
        // تنسيق عام للبيانات
        return $this->formatGenericResults($data);
    }

    protected function formatEmployeeResults($employees)
    {
        // تحويل إلى مجموعة من الكائنات
        $employeeObjects = collect($employees)->map(function($emp) {
            $empArray = (array)$emp;
            return (object)[
                'id' => $empArray['id'] ?? 0,
                'first_name' => $empArray['first_name'] ?? 'غير محدد',
                'last_name' => $empArray['last_name'] ?? '',
                'salary' => $empArray['salary'] ?? 0,
                'department' => (object)['dept_name' => $empArray['dept_name'] ?? 'غير محدد']
            ];
        });
        
        return ResponseFormatter::formatEmployeeList($employeeObjects);
    }

    protected function formatAttendanceResults($attendanceData)
    {
        // تحليل بيانات الحضور
        $stats = [
            'total' => count($attendanceData),
            'present' => 0,
            'late' => 0,
            'absent' => 0
        ];
        
        foreach ($attendanceData as $record) {
            $record = (array)$record;
            $status = $record['status'] ?? 'unknown';
            
            switch ($status) {
                case 'present':
                    $stats['present']++;
                    break;
                case 'late':
                    $stats['late']++;
                    break;
                case 'absent':
                    $stats['absent']++;
                    break;
            }
        }
        
        return ResponseFormatter::formatAttendanceToday($stats);
    }

    protected function formatSalaryResults($salaryData)
    {
        // تحضير بيانات الرواتب للتحليل
        $salaries = [];
        foreach ($salaryData as $record) {
            $record = (array)$record;
            $salary = $record['salary'] ?? $record['net_salary'] ?? 0;
            if ($salary > 0) {
                $salaries[] = ['salary' => $salary];
            }
        }
        
        return ResponseFormatter::formatSalaryInfo($salaries);
    }

    protected function formatDepartmentResults($departmentData)
    {
        // تحضير بيانات الأقسام
        $departments = [];
        foreach ($departmentData as $record) {
            $record = (array)$record;
            $departments[] = [
                'name' => $record['dept_name'] ?? 'غير محدد',
                'employee_count' => $record['count'] ?? $record['employee_count'] ?? 0
            ];
        }
        
        return ResponseFormatter::formatDepartmentStats($departments);
    }

    protected function formatGenericResults($data)
    {
        $response = "<div class='results-container'>";
        $response .= "<div class='results-header'>";
        $response .= "<h3>نتائج البحث</h3>";
        $response .= "<span class='count-badge'>" . count($data) . " نتيجة</span>";
        $response .= "</div>";
        
        foreach (array_slice($data, 0, 5) as $index => $row) {
            $row = (array)$row;
            $response .= "<div class='employee-card'>";
            $response .= "<div class='employee-header'>";
            $response .= "<span class='employee-name'>النتيجة " . ($index + 1) . "</span>";
            $response .= "</div>";
            $response .= "<div class='employee-details'>";
            
            $count = 0;
            foreach ($row as $key => $value) {
                if ($count >= 4) break; // عرض أول 4 حقول فقط
                
                $label = $this->translateFieldName($key);
                $formattedValue = $this->formatValue($key, $value);
                
                $response .= "<div class='detail-item'>";
                $response .= "<span class='label'>{$label}</span>";
                $response .= "<span class='value'>{$formattedValue}</span>";
                $response .= "</div>";
                $count++;
            }
            
            $response .= "</div>";
            $response .= "</div>";
        }
        
        if (count($data) > 5) {
            $remaining = count($data) - 5;
            $response .= "<div class='info-message'>يوجد {$remaining} نتيجة إضافية</div>";
        }
        
        $response .= "</div>";
        return $response;
    }

    protected function getContextLabel($context)
    {
        if (stripos($context, 'موظف') !== false) return 'عدد الموظفين';
        if (stripos($context, 'راتب') !== false) return 'متوسط الراتب';
        if (stripos($context, 'حضور') !== false) return 'إجمالي الحضور';
        if (stripos($context, 'قسم') !== false) return 'عدد الأقسام';
        
        return 'النتيجة';
    }

    protected function translateFieldName($field)
    {
        $translations = [
            'id' => 'الرقم',
            'first_name' => 'الاسم الأول',
            'last_name' => 'اسم العائلة',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'salary' => 'الراتب',
            'dept_name' => 'القسم',
            'hire_date' => 'تاريخ التوظيف',
            'status' => 'الحالة',
            'checkInTime' => 'وقت الحضور',
            'checkOutTime' => 'وقت الانصراف',
            'total' => 'الإجمالي',
            'count' => 'العدد'
        ];
        
        return $translations[$field] ?? $field;
    }

    protected function formatValue($key, $value)
    {
        if ($value === null || $value === '') {
            return 'غير محدد';
        }
        
        switch ($key) {
            case 'salary':
            case 'net_salary':
                return number_format($value) . ' جنيه';
                
            case 'hire_date':
            case 'date':
            case 'birthdate':
                return date('d/m/Y', strtotime($value));
                
            case 'checkInTime':
            case 'checkOutTime':
                return date('H:i', strtotime($value));
                
            case 'status':
                $statusMap = [
                    'present' => 'حاضر',
                    'absent' => 'غائب',
                    'late' => 'متأخر'
                ];
                return $statusMap[$value] ?? $value;
                
            default:
                return $value;
        }
    }

    protected function handleQueryError($error, $context)
    {
        if (strpos($error, 'Unknown column') !== false) {
            return "<div class='info-message'>عذراً، حدث خطأ في معالجة طلبك. يرجى إعادة صياغة السؤال بطريقة أخرى.</div>";
        }
        
        return "<div class='info-message'>لا أستطيع العثور على البيانات المطلوبة حالياً. يرجى المحاولة مرة أخرى.</div>";
    }

    protected function getFallbackResponse($message)
    {
        $message = strtolower(trim($message));
        
        // إجابات بسيطة للأسئلة الشائعة
        if (strpos($message, 'عدد الموظفين') !== false || strpos($message, 'كم موظف') !== false) {
            try {
                $count = Employee::count();
                return ResponseFormatter::formatEmployeeCount($count);
            } catch (\Exception $e) {
                return "<div class='info-message'>عذراً، لا أستطيع الوصول لبيانات الموظفين حالياً</div>";
            }
        }
        
        if (strpos($message, 'الأقسام') !== false || strpos($message, 'قسم') !== false) {
            try {
                $departments = Department::withCount('employees')->get();
                $deptData = [];
                foreach ($departments as $dept) {
                    $deptData[] = [
                        'name' => $dept->dept_name,
                        'employee_count' => $dept->employees_count
                    ];
                }
                return ResponseFormatter::formatDepartmentStats($deptData);
            } catch (\Exception $e) {
                return "<div class='info-message'>عذراً، لا أستطيع الوصول لبيانات الأقسام حالياً</div>";
            }
        }
        
        if (strpos($message, 'راتب') !== false || strpos($message, 'متوسط') !== false) {
            try {
                $salaries = Employee::whereNotNull('salary')->where('salary', '>', 0)->pluck('salary')->toArray();
                if (!empty($salaries)) {
                    $salaryData = array_map(function($salary) {
                        return ['salary' => $salary];
                    }, $salaries);
                    return ResponseFormatter::formatSalaryInfo($salaryData);
                }
                return "<div class='info-message'>لا توجد بيانات رواتب متاحة</div>";
            } catch (\Exception $e) {
                return "<div class='info-message'>عذراً، لا أستطيع حساب متوسط الراتب حالياً</div>";
            }
        }
        
        if (strpos($message, 'حضور') !== false || strpos($message, 'اليوم') !== false) {
            try {
                $attendanceStats = [
                    'total' => Attendence::whereDate('date', today())->count(),
                    'present' => Attendence::whereDate('date', today())->where('status', 'present')->count(),
                    'late' => Attendence::whereDate('date', today())->where('status', 'late')->count(),
                    'absent' => Attendence::whereDate('date', today())->where('status', 'absent')->count(),
                ];
                return ResponseFormatter::formatAttendanceToday($attendanceStats);
            } catch (\Exception $e) {
                return "<div class='info-message'>عذراً، لا أستطيع جلب بيانات الحضور حالياً</div>";
            }
        }
        
        return "<div class='general-response'>" .
               "<h3>مرحباً بك في نظام إدارة الموارد البشرية</h3>" .
               "<p>يمكنني مساعدتك في:</p>" .
               "<div style='margin: 15px 0;'>" .
               "<strong>معلومات الموظفين:</strong> عدد الموظفين، الأقسام، الرواتب<br>" .
               "<strong>بيانات الحضور:</strong> الحضور اليومي، التأخير، الساعات الإضافية<br>" .
               "<strong>كشوف المرتبات:</strong> الرواتب، الخصومات، المكافآت<br>" .
               "<strong>التقارير والإحصائيات:</strong> تحليلات مختلفة" .
               "</div>" .
               "<p><strong>مثال:</strong> اكتب \"كم عدد الموظفين؟\" أو \"اعرض الأقسام\"</p>" .
               "</div>";
    }

    public function getQuickStats()
    {
        try {
            return Cache::remember('hr_dashboard_stats', 300, function() {
                return [
                    'employees' => [
                        'total' => Employee::count(),
                        'active' => Employee::whereNotNull('hire_date')->count(),
                        'by_department' => Employee::join('departments', 'employees.department_id', '=', 'departments.id')
                                                   ->select('departments.dept_name', DB::raw('count(*) as count'))
                                                   ->groupBy('departments.dept_name')
                                                   ->get()->toArray()
                    ],
                    'attendance_today' => [
                        'total' => Attendence::whereDate('date', today())->count(),
                        'present' => Attendence::whereDate('date', today())->where('status', 'present')->count(),
                        'late' => Attendence::whereDate('date', today())->where('lateDurationInHours', '>', 0)->count(),
                    ],
                    'payroll_this_month' => [
                        'processed' => Payroll::whereMonth('created_at', now()->month)->count(),
                        'total_amount' => (float) Payroll::whereMonth('created_at', now()->month)->sum('net_salary'),
                    ]
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error getting quick stats', ['error' => $e->getMessage()]);
            return [
                'error' => 'Unable to fetch statistics at this time'
            ];
        }
    }

    // Method للاختبار البسيط
    public function testConnection()
    {
        try {
            Log::info('Testing OpenRouter connection');
            
            $response = $this->client->post($this->apiUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'قل "مرحبا" فقط']
                    ],
                    'max_tokens' => 10
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            
            Log::info('Test connection successful', ['response' => $responseBody]);
            
            return [
                'success' => true,
                'response' => $responseBody['choices'][0]['message']['content'] ?? 'No response',
                'model' => $responseBody['model'] ?? $this->model
            ];

        } catch (\Exception $e) {
            Log::error('Test connection failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}