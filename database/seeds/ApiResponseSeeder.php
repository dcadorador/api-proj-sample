<?php

use App\Api\V1\Models\ApiResponse;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ApiResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $message = [
            'en-us' => 'login successful',
            'ar-sa' => 'تسجيل الدخول بنجاح'
        ];

        ApiResponse::create([
            'code' => 1000,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'login failed, please check username or password',
            'ar-sa' => 'فشل تسجيل الدخول، يرجى التحقق من اسم المستخدم أو كلمة المرور'
        ];

        ApiResponse::create([
            'code' => 1001,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'register form incomplete',
            'ar-sa' => 'تسجيل نموذج غير مكتملة'
        ];

        ApiResponse::create([
            'code' => 1002,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'mobile number invalid',
            'ar-sa' => 'رقم الجوال غير صالح'
        ];

        ApiResponse::create([
            'code' => 1003,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'unable to send SMS pin code, please try again later',
            'ar-sa' => 'تعذر إرسال رمز بين سمز، الرجاء إعادة المحاولة لاحقا'
        ];

        ApiResponse::create([
            'code' => 1004,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'customer not found',
            'ar-sa' => 'لم يتم العثور على العميل'
        ];

        ApiResponse::create([
            'code' => 1005,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'customer address not found',
            'ar-sa' => 'لم يتم العثور على عنوان العميل'
        ];

        ApiResponse::create([
            'code' => 1006,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'could not create token',
            'ar-sa' => 'تعذر إنشاء رمز مميز'
        ];

        ApiResponse::create([
            'code' => 1007,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'username already exists',
            'ar-sa' => 'اسم المستخدم موجود بالفعل'
        ];

        ApiResponse::create([
            'code' => 1008,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'location not found',
            'ar-sa' => 'لم يتم العثور على الموقع'
        ];

        ApiResponse::create([
            'code' => 1009,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'delivery area not found',
            'ar-sa' => 'لم يتم العثور على منطقة التسليم'
        ];

        ApiResponse::create([
            'code' => 1010,
            'message' => $message
        ]);


        $message = [
            'en-us' => 'we do not deliver to your area',
            'ar-sa' => 'لم يتم العثور على منطقة التسليم'
        ];

        ApiResponse::create([
            'code' => 1011,
            'message' => $message
        ]);


        $message = [
            'en-us' => 'pin is invalid',
            'ar-sa' => 'دبوس غير صالح'
        ];

        ApiResponse::create([
            'code' => 1012,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'employee not found',
            'ar-sa' => 'لم يتم العثور على الموظف'
        ];

        ApiResponse::create([
            'code' => 1013,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'concept not found',
            'ar-sa' => 'لم يتم العثور على مفهوم'
        ];

        ApiResponse::create([
            'code' => 1014,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'bundled category not found',
            'ar-sa' => 'لم يتم العثور على الفئة المجمعة'
        ];

        ApiResponse::create([
            'code' => 1015,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'bundled item not found',
            'ar-sa' => 'لم يتم العثور على العنصر المجمع'
        ];

        ApiResponse::create([
            'code' => 1016,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'category not found',
            'ar-sa' => 'لم يتم العثور على الفئة'
        ];

        ApiResponse::create([
            'code' => 1017,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'menu not found',
            'ar-sa' => 'لم يتم العثور على القائمة'
        ];

        ApiResponse::create([
            'code' => 1018,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'password format invalid, min. 8 characters, must have 1 letter or number',
            'ar-sa' => 'تنسيق كلمة المرور غير صالح، دقيقة. 8 أحرف، يجب أن يكون 1 حرف أو رقم'
        ];

        ApiResponse::create([
            'code' => 1019,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'order not found',
            'ar-sa' => 'لم يتم العثور على الطلب'
        ];

        ApiResponse::create([
            'code' => 1020,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'bearing not found',
            'ar-sa' => 'لم يتم العثور عليها'
        ];

        ApiResponse::create([
            'code' => 1021,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'orders not found',
            'ar-sa' => 'لم يتم العثور على الطلبات'
        ];

        ApiResponse::create([
            'code' => 1022,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'field not found',
            'ar-sa' => 'لم يتم العثور على الحقل'
        ];

        ApiResponse::create([
            'code' => 1023,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'device not found',
            'ar-sa' => 'لم يتم العثور على الحقل'
        ];

        ApiResponse::create([
            'code' => 1024,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'incorrect filter parameter',
            'ar-sa' => 'معلمة تصفية غير صحيحة'
        ];

        ApiResponse::create([
            'code' => 1025,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'unable to process order, total is less than minimum amount',
            'ar-sa' => 'غير قادر على معالجة النظام، مجموع هو أقل من الحد الأدنى للمبلغ'
        ];

        ApiResponse::create([
            'code' => 1026,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'unable to process order, please try again later',
            'ar-sa' => 'غير قادر على معالجة الطلب، الرجاء إعادة المحاولة لاحقا'
        ];

        ApiResponse::create([
            'code' => 1027,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'payfort gateway error',
            'ar-sa' => 'خطأ بوابة بايفورت'
        ];

        ApiResponse::create([
            'code' => 1028,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'image size exceeds the 10MB limit',
            'ar-sa' => 'حجم الصورة يتجاوز الحد 10MB'
        ];

        ApiResponse::create([
            'code' => 1029,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'form incomplete',
            'ar-sa' => 'شكل غير كامل'
        ];

        ApiResponse::create([
            'code' => 1030,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'branch rejects online orders',
            'ar-sa' => 'فرع يرفض الطلبات عبر الإنترنت'
        ];

        ApiResponse::create([
            'code' => 1031,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'required modifier missing',
            'ar-sa' => 'المطلوب معدل مفقود'
        ];

        ApiResponse::create([
            'code' => 1032,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'due date outside working hours',
            'ar-sa' => 'تاريخ الاستحقاق خارج ساعات العمل'
        ];

        ApiResponse::create([
            'code' => 1033,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'modifier not allowing multiple options',
            'ar-sa' => 'معدل لا يسمح خيارات متعددة'
        ];

        ApiResponse::create([
            'code' => 1034,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'non optional ingredient removed',
            'ar-sa' => 'تمت إزالة العنصر غير الاختياري'
        ];

        ApiResponse::create([
            'code' => 1035,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'invalid payment method type',
            'ar-sa' => 'نوع طريقة الدفع غير صالح'
        ];

        ApiResponse::create([
            'code' => 1036,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'invalid removed ingredient',
            'ar-sa' => 'عنصر إزالة غير صالح'
        ];

        ApiResponse::create([
            'code' => 1037,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'item not found',
            'ar-sa' => 'العنصر غير موجود'
        ];

        ApiResponse::create([
            'code' => 1038,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'favorite item not found',
            'ar-sa' => 'لم يتم العثور على العنصر المفضل'
        ];

        ApiResponse::create([
            'code' => 1039,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'ingredient not found',
            'ar-sa' => 'لم يتم العثور على المكون'
        ];

        ApiResponse::create([
            'code' => 1040,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'item ingredient not found',
            'ar-sa' => 'لم يتم العثور على عنصر العنصر'
        ];

        ApiResponse::create([
            'code' => 1041,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'order status not found',
            'ar-sa' => 'لم يتم العثور على حالة الطلب'
        ];

        ApiResponse::create([
            'code' => 1042,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'unable to send coordinates. please check number of order with status of: delivery in progress',
            'ar-sa' => 'غير قادر على إرسال الإحداثيات. يرجى التحقق من عدد من النظام مع حالة: التسليم في التقدم'
        ];

        ApiResponse::create([
            'code' => 1043,
            'message' => $message
        ]);

        $message = [
            'en-us' => 'order item not found',
            'ar-sa' => 'لم يتم العثور على عنصر الطلب'
        ];

        ApiResponse::create([
            'code' => 1044,
            'message' => $message
        ]);

    }
}