<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LetterPdfController;
use App\Http\Controllers\ReceiptVerifyController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\GalleryPageController;
use App\Http\Controllers\ExtracurricularPageController;
use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\PublicPpdbController;
use App\Http\Controllers\Operator\PpdbController as OperatorPpdb;
use App\Http\Controllers\Kamad\PpdbController as KamadPpdb;

// Profile
use App\Http\Controllers\ProfileController;

// Kamad
use App\Http\Controllers\Kamad\DashboardController as KamadDashboard;
use App\Http\Controllers\Kamad\AcademicYearController as KamadAcademicYear;
use App\Http\Controllers\Kamad\ReportCardController as KamadReportCard;
use App\Http\Controllers\Kamad\LetterController as KamadLetter;
use App\Http\Controllers\Kamad\HonorariumController as KamadHonorarium;

// Operator
use App\Http\Controllers\Operator\DashboardController as OperatorDashboard;
use App\Http\Controllers\Operator\AcademicYearController as OperatorAcademicYear;
use App\Http\Controllers\Operator\LetterController as OperatorLetter;
use App\Http\Controllers\Operator\ClassroomController;
use App\Http\Controllers\Operator\TeacherController;
use App\Http\Controllers\Operator\TeacherSubjectController;
use App\Http\Controllers\Operator\SubjectController;
use App\Http\Controllers\Operator\StudentController;
use App\Http\Controllers\Operator\PredicateConfigController;
use App\Http\Controllers\Operator\AssessmentComponentController;
use App\Http\Controllers\Operator\SchoolSettingController;
use App\Http\Controllers\Operator\ExtracurricularController;
use App\Http\Controllers\Operator\SchoolGalleryController;
use App\Http\Controllers\Operator\LetterTypeController;
use App\Http\Controllers\Operator\LetterTemplateController;
use App\Http\Controllers\Operator\TeachingHourController;

// Keuangan
use App\Http\Controllers\Keuangan\DashboardController as KeuanganDashboard;
use App\Http\Controllers\Keuangan\InvoiceController as KeuanganInvoice;
use App\Http\Controllers\Keuangan\PaymentController as KeuanganPayment;
use App\Http\Controllers\Keuangan\PaymentTypeController;
use App\Http\Controllers\Keuangan\ReportController as KeuanganReport;
use App\Http\Controllers\Keuangan\HonorariumController as KeuanganHonorarium;

// Guru
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Guru\ReportCardController as GuruReportCard;
use App\Http\Controllers\Guru\AssessmentController;
use App\Http\Controllers\Guru\AttendanceController as GuruAttendance;

// Siswa
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboard;
use App\Http\Controllers\Siswa\ReportCardController as SiswaReportCard;
use App\Http\Controllers\Siswa\InvoiceController as SiswaInvoice;
use App\Http\Controllers\Siswa\PaymentController as SiswaPayment;
use App\Http\Controllers\Siswa\LetterController as SiswaLetter;

// Landing page
Route::get('/', WelcomeController::class)->name('welcome');
Route::get('/tentang', AboutPageController::class)->name('tentang');
Route::get('/galeri', GalleryPageController::class)->name('galeri');
Route::get('/ekskul', ExtracurricularPageController::class)->name('ekskul');

// PPDB public — rate limit: 10 submit/menit per IP
Route::get('/ppdb', [PublicPpdbController::class, 'index'])->name('ppdb.index');
Route::post('/ppdb/daftar', [PublicPpdbController::class, 'store'])->middleware('throttle:10,1')->name('ppdb.store');
Route::get('/ppdb/cek', [PublicPpdbController::class, 'check'])->name('ppdb.check');

// Verify barcode letters — rate limit: 30 req/menit per IP
Route::get('verify/{barcodeCode}', [KamadLetter::class, 'verify'])
    ->middleware('throttle:30,1')
    ->name('letters.verify');

// Verify receipt kwitansi pembayaran — rate limit: 30 req/menit per IP
Route::get('receipt/{code}', [ReceiptVerifyController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('receipt.verify');

// Verify raport — rate limit: 30 req/menit per IP
Route::get('verify-raport/{verifyCode}', [KamadReportCard::class, 'verify'])
    ->middleware('throttle:30,1')
    ->name('report-cards.verify');

// Midtrans callback — rate limit: 60 req/menit (Midtrans bisa retry)
Route::post('midtrans/callback', [SiswaPayment::class, 'callback'])
    ->middleware('throttle:60,1')
    ->name('midtrans.callback');

// Middleware auth + verified untuk semua route 
Route::middleware(['auth', 'verified'])->group(function () {

    // Kamad Routes
    Route::middleware('role:kamad')->prefix('kamad')->name('kamad.')->group(function () {
        Route::get('/dashboard', KamadDashboard::class)->name('dashboard');
        Route::resource('academic-years', KamadAcademicYear::class);

        // Approve Tahun Ajaran
        Route::get('/academic-years', [KamadAcademicYear::class, 'index'])->name('academic-years.index');
        Route::patch('/academic-years/{academicYear}/approve', [KamadAcademicYear::class, 'approve'])->name('academic-years.approve');

        // Report Card
        Route::get('/report-cards', [KamadReportCard::class, 'index'])->name('report-cards.index');
        Route::post('/report-cards/{classroom}/generate', [KamadReportCard::class, 'generate'])->name('report-cards.generate');
        Route::patch('/report-cards/{reportCard}/approve', [KamadReportCard::class, 'approve'])->name('report-cards.approve');
        Route::patch('/report-cards/{classroom}/approve-all', [KamadReportCard::class, 'approveAll'])->name('report-cards.approve-all');

        // Letter Approvals
        Route::get('letters', [KamadLetter::class, 'index'])->name('letters.index');
        Route::patch('letters/{letter}/approve', [KamadLetter::class, 'approve'])->name('letters.approve');
        Route::patch('letters/{letter}/reject', [KamadLetter::class, 'reject'])->name('letters.reject');
        Route::get('letters/{letter}/pdf', [LetterPdfController::class, 'download'])->name('letters.pdf');

        // Honor Guru (view only)
        Route::get('honorariums', [KamadHonorarium::class, 'index'])->name('honorariums.index');

        // PPDB (view only)
        Route::get('ppdb', [KamadPpdb::class, 'index'])->name('ppdb.index');
    });

    // Operator Routes
    Route::middleware('role:operator')->prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', OperatorDashboard::class)->name('dashboard');

        // Create Tahun Ajaran
        Route::get('/academic-years', [OperatorAcademicYear::class, 'index'])->name('academic-years.index');
        Route::post('/academic-years', [OperatorAcademicYear::class, 'store'])->name('academic-years.store');

        // Create Guru
        Route::resource('teachers', TeacherController::class)->except(['create', 'edit']);

        // Create Kelas
        Route::resource('classrooms', ClassroomController::class)->except(['create', 'edit']);
        Route::get('classrooms/{classroom}/available-students', [ClassroomController::class, 'availableStudents'])
             ->name('classrooms.available-students');
        Route::post('classrooms/{classroom}/assign-students', [ClassroomController::class, 'assignStudents'])
             ->name('classrooms.assign-students');
        Route::post('classrooms/{classroom}/assign-guru-kelas', [ClassroomController::class, 'assignGuruKelas'])
             ->name('classrooms.assign-guru-kelas');
        Route::post('classrooms/{classroom}/assign-wali-kelas', [ClassroomController::class, 'assignWaliKelas'])
             ->name('classrooms.assign-wali-kelas');
        Route::post('classrooms/{classroom}/assign-guru-bidang', [ClassroomController::class, 'assignGuruBidang'])
             ->name('classrooms.assign-guru-bidang');
        Route::get('classrooms/{classroom}/available-teachers', [ClassroomController::class, 'availableTeachers'])
             ->name('classrooms.available-teachers');

        // Create Mata Pelajaran
        Route::resource('subjects', SubjectController::class)->except(['create', 'edit']);

        // Create Siswa
        Route::resource('students', StudentController::class)->except(['create', 'edit']);
        Route::patch('students/{student}/assign-classroom', [StudentController::class, 'assignClassroom'])
            ->name('students.assign-classroom');

        // Teacher-Subject Assignment
        Route::resource('teacher-subjects', TeacherSubjectController::class)
            ->only(['index', 'store', 'destroy']);

        // Create Assessment Component & Predicate Config
        Route::get('/predicate-configs', [PredicateConfigController::class, 'index'])->name('predicate-configs.index');
        Route::post('/predicate-configs/{academicYear}/sync', [PredicateConfigController::class, 'sync'])->name('predicate-configs.sync');
        Route::resource('assessment-components', AssessmentComponentController::class)->except(['create', 'edit', 'show']);

        // Letter Types & Templates Settings
        Route::get('school-settings', [SchoolSettingController::class, 'index'])->name('school-settings.index');
        Route::post('school-settings', [SchoolSettingController::class, 'save'])->name('school-settings.save');
        Route::resource('extracurriculars', ExtracurricularController::class)->except(['create', 'edit', 'show']);
        Route::resource('school-galleries', SchoolGalleryController::class)->only(['index', 'store', 'destroy']);
        Route::resource('letter-types', LetterTypeController::class)->except(['create', 'edit', 'show']);
        Route::resource('letter-templates', LetterTemplateController::class)->except(['create', 'edit', 'show']);
        Route::get('letters', [OperatorLetter::class, 'index'])->name('letters.index');
        Route::post('letters/notification', [OperatorLetter::class, 'storeNotification'])->name('letters.store-notification');
        Route::patch('letters/{letter}/submit', [OperatorLetter::class, 'submitForApproval'])->name('letters.submit');
        Route::get('letters/{letter}/pdf', [LetterPdfController::class, 'download'])->name('letters.pdf');

        // Jam Pelajaran Guru
        Route::get('teaching-hours', [TeachingHourController::class, 'index'])->name('teaching-hours.index');
        Route::post('teaching-hours', [TeachingHourController::class, 'store'])->name('teaching-hours.store');
        Route::delete('teaching-hours/{teachingHour}', [TeachingHourController::class, 'destroy'])->name('teaching-hours.destroy');

        // PPDB
        Route::get('ppdb', [OperatorPpdb::class, 'index'])->name('ppdb.index');
        Route::post('ppdb/settings', [OperatorPpdb::class, 'saveSetting'])->name('ppdb.save-setting');
        Route::patch('ppdb/registrations/{registration}/accept', [OperatorPpdb::class, 'accept'])->name('ppdb.accept');
        Route::patch('ppdb/registrations/{registration}/reject', [OperatorPpdb::class, 'reject'])->name('ppdb.reject');
        Route::patch('ppdb/registrations/{registration}/waitlist', [OperatorPpdb::class, 'waitlist'])->name('ppdb.waitlist');
    });

    // TU Keuangan Routes
    Route::middleware('role:tu_keuangan')->prefix('keuangan')->name('keuangan.')->group(function () {

        // Dashboard
        Route::get('/dashboard', KeuanganDashboard::class)->name('dashboard');

        // Payment Types & Invoices
        Route::resource('payment-types', PaymentTypeController::class)->except(['create', 'edit', 'show']);
        Route::post('payment-types/generate-spp', [PaymentTypeController::class, 'generateSpp'])->name('payment-types.generate-spp');
        Route::get('invoices', [KeuanganInvoice::class, 'index'])->name('invoices.index');
        Route::get('invoices/{student}', [KeuanganInvoice::class, 'show'])->name('invoices.show');
        Route::post('invoices/{invoice}/payments', [KeuanganPayment::class, 'store'])->name('payments.store');
        Route::delete('payments/{payment}', [KeuanganPayment::class, 'destroy'])->name('payments.destroy');
        Route::get('invoices/{invoice}/receipt', [KeuanganPayment::class, 'receipt'])->name('payments.receipt');
        Route::get('invoices/{invoice}/receipt/pdf', [KeuanganPayment::class, 'receiptPdf'])->name('payments.receipt-pdf');
        Route::get('reports/export', [KeuanganReport::class, 'export'])->name('reports.export');

        // Honor Guru
        Route::get('honorariums', [KeuanganHonorarium::class, 'index'])->name('honorariums.index');
        Route::post('honorariums/generate', [KeuanganHonorarium::class, 'generate'])->name('honorariums.generate');
        Route::patch('honorariums/{honorarium}/mark-paid', [KeuanganHonorarium::class, 'markPaid'])->name('honorariums.mark-paid');
        Route::delete('honorariums/{honorarium}', [KeuanganHonorarium::class, 'destroy'])->name('honorariums.destroy');
        Route::get('honorariums/{honorarium}/slip', [KeuanganHonorarium::class, 'downloadSlip'])->name('honorariums.slip');
    });

    // Guru Routes
    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {

        // Dashboard
        Route::get('/dashboard', GuruDashboard::class)->name('dashboard');

        // Raport
        Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/{classroom}/{assessmentComponent}', [AssessmentController::class, 'show'])->name('assessments.show');
        Route::post('/assessments/{assessmentComponent}/bulk', [AssessmentController::class, 'bulkStore'])->name('assessments.bulk-store');
        Route::get('/report-cards', [GuruReportCard::class, 'index'])->name('report-cards.index');
        Route::patch('/report-cards/{reportCard}/notes', [GuruReportCard::class, 'updateNotes'])->name('report-cards.update-notes');
        Route::patch('/report-cards/{reportCard}/submit', [GuruReportCard::class, 'submit'])->name('report-cards.submit');
        Route::get('/report-cards/{reportCard}/export', [GuruReportCard::class, 'export'])->name('report-cards.export');

        // Absensi Guru
        Route::get('/attendance', [GuruAttendance::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [GuruAttendance::class, 'store'])->name('attendance.store');
        Route::patch('/attendance/{attendance}', [GuruAttendance::class, 'update'])->name('attendance.update');
        Route::delete('/attendance/{attendance}', [GuruAttendance::class, 'destroy'])->name('attendance.destroy');
    });

    // Siswa Routes
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {

        // Dashboard
        Route::get('/dashboard', SiswaDashboard::class)->name('dashboard');

        // Raport
        Route::get('/report-cards', [SiswaReportCard::class, 'index'])->name('report-cards.index');
        Route::get('/report-cards/{semester}', [SiswaReportCard::class, 'show'])->name('report-cards.show');

        // Pembayaran
        Route::get('invoices', [SiswaInvoice::class, 'index'])->name('invoices.index');
        Route::post('invoices/{invoice}/request-cash', [SiswaPayment::class, 'requestCash'])->name('payments.request-cash');
        Route::post('invoices/{invoice}/pay', [SiswaPayment::class, 'initiate'])->name('payments.initiate');
        Route::post('invoices/{invoice}/verify-payment', [SiswaPayment::class, 'verify'])->name('payments.verify');
        Route::get('payment/finish', [SiswaPayment::class, 'finish'])->name('payments.finish');
        Route::get('invoices/{invoice}/receipt', [SiswaPayment::class, 'receipt'])->name('payments.receipt');
        Route::get('invoices/{invoice}/receipt/pdf', [SiswaPayment::class, 'receiptPdf'])->name('payments.receipt-pdf');

        // Request Letters
        Route::get('letters', [SiswaLetter::class, 'index'])->name('letters.index');
        Route::post('letters', [SiswaLetter::class, 'store'])->name('letters.store');
        Route::get('letters/{letter}', [SiswaLetter::class, 'show'])->name('letters.show');
        Route::patch('letters/{letter}/read', [SiswaLetter::class, 'markAsRead'])->name('letters.read');
        Route::get('letters/{letter}/pdf', [LetterPdfController::class, 'download'])->name('letters.pdf');
    });

    // Notifications (semua role)
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Profile (semua role bisa akses)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
