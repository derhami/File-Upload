<?php
// تنظیمات
$uploadDirectory = __DIR__ . '/'; // تغییر دادن مسیر آپلود به ریشه فایل ها

// تابع برای ایجاد نام فایل هش شده با پسوند فرمت
function generateHashedFileName($uploadedFileName)
{
    $fileExtension = strtolower(pathinfo($uploadedFileName, PATHINFO_EXTENSION));
    $hash = hash('sha256', $uploadedFileName . time()); // می‌توانید از الگوریتم دیگری نیز استفاده کنید

    return "ak-$hash.$fileExtension";
}

// ایجاد دایرکتوری مقصد اگر وجود نداشته باشد
if (!file_exists($uploadDirectory)) {
    mkdir($uploadDirectory, 0777, true);
}

// بررسی ارسال فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // بررسی رمز
    $validPassword = 'your_password'; // رمز مورد نظر
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if ($password !== $validPassword) {
        die(json_encode(['message' => 'رمز اشتباه است.', 'success' => false]));
    }

    // بررسی آیا فایل انتخاب شده است
    if (!isset($_FILES['file'])) {
        die(json_encode(['message' => 'هیچ فایلی انتخاب نشده است.', 'success' => false]));
    }

    $uploadedFile = $_FILES['file'];

    // بررسی فرمت مجاز
    $allowedExtensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        die(json_encode(['message' => 'پسوند فایل مجاز نیست.', 'success' => false]));
    }

    // بررسی خطاهای آپلود
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['message' => 'خطا در آپلود فایل.', 'success' => false]));
    }

    // بررسی حجم فایل
    $maxFileSize = 5 * 1024 * 1024; // حداکثر حجم مجاز (در اینجا 5 مگابایت)
    if ($uploadedFile['size'] > $maxFileSize) {
        die(json_encode(['message' => 'حجم فایل بیش از حد مجاز است.', 'success' => false]));
    }

    $fileName = generateHashedFileName(basename($uploadedFile['name']));

    $destination = $uploadDirectory . $fileName;

    // انتقال فایل به مسیر آپلود
    if (move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
        $fileLink = 'https://adrienkesht.com/uploads/' . $fileName;
        die(json_encode(['message' => 'فایل با موفقیت آپلود شد.', 'success' => true, 'fileLink' => $fileLink]));
    } else {
        die(json_encode(['message' => 'خطا در انتقال فایل.', 'success' => false]));
    }
}
?>
