# File Tree: school-management

**Generated:** 6/9/2026, 11:01:00 PM
**Root Path:** `c:\laragon\www\school-management`

```
├── 📁 app
│   ├── 📁 Console
│   │   └── 📁 Commands
│   │       └── 🐘 GeneratePwaIcons.php
│   ├── 📁 Exports
│   │   ├── 📁 Sheets
│   │   │   ├── 🐘 PaymentTypeSheet.php
│   │   │   └── 🐘 SummarySheet.php
│   │   ├── 🐘 FinancialReportExport.php
│   │   ├── 🐘 StudentAccountsExport.php
│   │   ├── 🐘 StudentAccountsSheet.php
│   │   ├── 🐘 StudentsExport.php
│   │   ├── 🐘 TeacherAttendanceRecapExport.php
│   │   └── 🐘 TeachersExport.php
│   ├── 📁 Helpers
│   │   └── 🐘 QrCodeHelper.php
│   ├── 📁 Http
│   │   ├── 📁 Controllers
│   │   │   ├── 📁 Auth
│   │   │   │   ├── 🐘 AuthenticatedSessionController.php
│   │   │   │   ├── 🐘 ConfirmablePasswordController.php
│   │   │   │   ├── 🐘 EmailVerificationNotificationController.php
│   │   │   │   ├── 🐘 EmailVerificationPromptController.php
│   │   │   │   ├── 🐘 NewPasswordController.php
│   │   │   │   ├── 🐘 PasswordController.php
│   │   │   │   ├── 🐘 PasswordResetLinkController.php
│   │   │   │   ├── 🐘 RegisteredUserController.php
│   │   │   │   └── 🐘 VerifyEmailController.php
│   │   │   ├── 📁 Guru
│   │   │   │   ├── 🐘 AssessmentController.php
│   │   │   │   ├── 🐘 AttendanceController.php
│   │   │   │   ├── 🐘 DashboardController.php
│   │   │   │   └── 🐘 ReportCardController.php
│   │   │   ├── 📁 Kamad
│   │   │   │   ├── 🐘 AcademicYearController.php
│   │   │   │   ├── 🐘 DashboardController.php
│   │   │   │   ├── 🐘 HonorariumController.php
│   │   │   │   ├── 🐘 LetterController.php
│   │   │   │   ├── 🐘 PpdbController.php
│   │   │   │   ├── 🐘 ReportCardController.php
│   │   │   │   └── 🐘 TeacherAttendanceRecapController.php
│   │   │   ├── 📁 Keuangan
│   │   │   │   ├── 🐘 DashboardController.php
│   │   │   │   ├── 🐘 HonorariumController.php
│   │   │   │   ├── 🐘 InvoiceController.php
│   │   │   │   ├── 🐘 PaymentController.php
│   │   │   │   ├── 🐘 PaymentTypeController.php
│   │   │   │   ├── 🐘 PpdbInvoiceController.php
│   │   │   │   └── 🐘 ReportController.php
│   │   │   ├── 📁 Operator
│   │   │   │   ├── 🐘 AcademicYearController.php
│   │   │   │   ├── 🐘 AssessmentComponentController.php
│   │   │   │   ├── 🐘 ClassroomController.php
│   │   │   │   ├── 🐘 DashboardController.php
│   │   │   │   ├── 🐘 ExtracurricularController.php
│   │   │   │   ├── 🐘 LetterController.php
│   │   │   │   ├── 🐘 LetterTemplateController.php
│   │   │   │   ├── 🐘 LetterTypeController.php
│   │   │   │   ├── 🐘 PpdbController.php
│   │   │   │   ├── 🐘 PredicateConfigController.php
│   │   │   │   ├── 🐘 SchoolGalleryController.php
│   │   │   │   ├── 🐘 SchoolHeroPhotoController.php
│   │   │   │   ├── 🐘 SchoolPostController.php
│   │   │   │   ├── 🐘 SchoolSettingController.php
│   │   │   │   ├── 🐘 StaffController.php
│   │   │   │   ├── 🐘 StudentController.php
│   │   │   │   ├── 🐘 StudentImportExportController.php
│   │   │   │   ├── 🐘 SubjectController.php
│   │   │   │   ├── 🐘 TeacherAttendanceRecapController.php
│   │   │   │   ├── 🐘 TeacherController.php
│   │   │   │   ├── 🐘 TeacherImportExportController.php
│   │   │   │   ├── 🐘 TeacherSubjectController.php
│   │   │   │   └── 🐘 TeachingHourController.php
│   │   │   ├── 📁 Siswa
│   │   │   │   ├── 🐘 DashboardController.php
│   │   │   │   ├── 🐘 InvoiceController.php
│   │   │   │   ├── 🐘 LetterController.php
│   │   │   │   ├── 🐘 PaymentController.php
│   │   │   │   └── 🐘 ReportCardController.php
│   │   │   ├── 🐘 AboutPageController.php
│   │   │   ├── 🐘 Controller.php
│   │   │   ├── 🐘 ExtracurricularDetailController.php
│   │   │   ├── 🐘 ExtracurricularPageController.php
│   │   │   ├── 🐘 GalleryPageController.php
│   │   │   ├── 🐘 HonorariumVerifyController.php
│   │   │   ├── 🐘 LetterPdfController.php
│   │   │   ├── 🐘 ManifestController.php
│   │   │   ├── 🐘 NotificationController.php
│   │   │   ├── 🐘 ProfileController.php
│   │   │   ├── 🐘 PublicPpdbController.php
│   │   │   ├── 🐘 PublicSchoolPostController.php
│   │   │   ├── 🐘 ReceiptVerifyController.php
│   │   │   └── 🐘 WelcomeController.php
│   │   ├── 📁 Middleware
│   │   │   ├── 🐘 CheckAlumniExpiry.php
│   │   │   └── 🐘 HandleInertiaRequests.php
│   │   └── 📁 Requests
│   │       ├── 📁 Auth
│   │       │   └── 🐘 LoginRequest.php
│   │       ├── 🐘 BulkInputScoreRequest.php
│   │       ├── 🐘 GenerateHonorariumRequest.php
│   │       ├── 🐘 GenerateSppRequest.php
│   │       ├── 🐘 ProfileUpdateRequest.php
│   │       ├── 🐘 RecordCashPaymentRequest.php
│   │       ├── 🐘 RejectLetterRequest.php
│   │       ├── 🐘 RequestLetterRequest.php
│   │       ├── 🐘 SaveSchoolSettingRequest.php
│   │       ├── 🐘 SetTeachingHourRequest.php
│   │       ├── 🐘 StoreAcademicYearRequest.php
│   │       ├── 🐘 StoreAssessmentComponentRequest.php
│   │       ├── 🐘 StoreClassroomRequest.php
│   │       ├── 🐘 StoreExtracurricularAchievementRequest.php
│   │       ├── 🐘 StoreExtracurricularRequest.php
│   │       ├── 🐘 StoreLetterTemplateRequest.php
│   │       ├── 🐘 StoreLetterTypeRequest.php
│   │       ├── 🐘 StoreNotificationLetterRequest.php
│   │       ├── 🐘 StorePaymentTypeRequest.php
│   │       ├── 🐘 StorePpdbRegistrationRequest.php
│   │       ├── 🐘 StorePpdbSettingRequest.php
│   │       ├── 🐘 StoreSchoolGalleryRequest.php
│   │       ├── 🐘 StoreSchoolPostRequest.php
│   │       ├── 🐘 StoreStudentRequest.php
│   │       ├── 🐘 StoreSubjectRequest.php
│   │       ├── 🐘 StoreTeacherAttendanceRequest.php
│   │       ├── 🐘 StoreTeacherRequest.php
│   │       ├── 🐘 StoreTeacherSubjectRequest.php
│   │       ├── 🐘 SyncPredicateConfigRequest.php
│   │       ├── 🐘 UpdateAssessmentComponentRequest.php
│   │       ├── 🐘 UpdateClassroomRequest.php
│   │       ├── 🐘 UpdateExtracurricularAchievementRequest.php
│   │       ├── 🐘 UpdateExtracurricularRequest.php
│   │       ├── 🐘 UpdateLetterTemplateRequest.php
│   │       ├── 🐘 UpdateLetterTypeRequest.php
│   │       ├── 🐘 UpdatePaymentTypeRequest.php
│   │       ├── 🐘 UpdateReportCardNotesRequest.php
│   │       ├── 🐘 UpdateStudentRequest.php
│   │       ├── 🐘 UpdateSubjectRequest.php
│   │       ├── 🐘 UpdateTeacherAttendanceRequest.php
│   │       └── 🐘 UpdateTeacherRequest.php
│   ├── 📁 Imports
│   │   ├── 🐘 StudentsImportPreview.php
│   │   └── 🐘 TeachersImportPreview.php
│   ├── 📁 Jobs
│   │   ├── 🐘 SendHonorariumSlipJob.php
│   │   └── 🐘 SendSppReminderJob.php
│   ├── 📁 Models
│   │   ├── 🐘 AcademicYear.php
│   │   ├── 🐘 AssessmentComponent.php
│   │   ├── 🐘 Classroom.php
│   │   ├── 🐘 Extracurricular.php
│   │   ├── 🐘 ExtracurricularAchievement.php
│   │   ├── 🐘 ExtracurricularPhoto.php
│   │   ├── 🐘 Invoice.php
│   │   ├── 🐘 Letter.php
│   │   ├── 🐘 LetterRecipient.php
│   │   ├── 🐘 LetterTemplate.php
│   │   ├── 🐘 LetterType.php
│   │   ├── 🐘 Notification.php
│   │   ├── 🐘 Payment.php
│   │   ├── 🐘 PaymentRequest.php
│   │   ├── 🐘 PaymentType.php
│   │   ├── 🐘 PpdbRegistration.php
│   │   ├── 🐘 PpdbSetting.php
│   │   ├── 🐘 PredicateConfig.php
│   │   ├── 🐘 ReportCard.php
│   │   ├── 🐘 ReportCardNote.php
│   │   ├── 🐘 SchoolGallery.php
│   │   ├── 🐘 SchoolHeroPhoto.php
│   │   ├── 🐘 SchoolPost.php
│   │   ├── 🐘 SchoolPostImage.php
│   │   ├── 🐘 SchoolSetting.php
│   │   ├── 🐘 Student.php
│   │   ├── 🐘 StudentAssessment.php
│   │   ├── 🐘 StudentClassroom.php
│   │   ├── 🐘 Subject.php
│   │   ├── 🐘 Teacher.php
│   │   ├── 🐘 TeacherAttendance.php
│   │   ├── 🐘 TeacherHonorarium.php
│   │   ├── 🐘 TeacherSubject.php
│   │   ├── 🐘 TeacherTeachingHour.php
│   │   └── 🐘 User.php
│   ├── 📁 Providers
│   │   └── 🐘 AppServiceProvider.php
│   └── 📁 Services
│       ├── 🐘 AcademicYearService.php
│       ├── 🐘 AssessmentComponentService.php
│       ├── 🐘 ClassroomService.php
│       ├── 🐘 ExtracurricularService.php
│       ├── 🐘 FinancialReportService.php
│       ├── 🐘 InvoiceService.php
│       ├── 🐘 LetterService.php
│       ├── 🐘 LetterTemplateService.php
│       ├── 🐘 LetterTypeService.php
│       ├── 🐘 NotificationService.php
│       ├── 🐘 PaymentService.php
│       ├── 🐘 PaymentTypeService.php
│       ├── 🐘 PpdbService.php
│       ├── 🐘 PredicateConfigService.php
│       ├── 🐘 ReportCardService.php
│       ├── 🐘 SchoolGalleryService.php
│       ├── 🐘 SchoolPostService.php
│       ├── 🐘 SchoolSettingService.php
│       ├── 🐘 StudentAssessmentService.php
│       ├── 🐘 StudentService.php
│       ├── 🐘 SubjectService.php
│       ├── 🐘 TeacherAttendanceService.php
│       ├── 🐘 TeacherHonorariumService.php
│       ├── 🐘 TeacherService.php
│       ├── 🐘 TeacherSubjectService.php
│       ├── 🐘 TeachingHourService.php
│       └── 🐘 WhatsAppService.php
├── 📁 bootstrap
│   ├── 🐘 app.php
│   └── 🐘 providers.php
├── 📁 config
│   ├── 🐘 app.php
│   ├── 🐘 auth.php
│   ├── 🐘 cache.php
│   ├── 🐘 database.php
│   ├── 🐘 excel.php
│   ├── 🐘 filesystems.php
│   ├── 🐘 logging.php
│   ├── 🐘 mail.php
│   ├── 🐘 permission.php
│   ├── 🐘 queue.php
│   ├── 🐘 services.php
│   └── 🐘 session.php
├── 📁 database
│   ├── 📁 factories
│   │   └── 🐘 UserFactory.php
│   ├── 📁 migrations
│   │   ├── 🐘 0001_01_01_000000_create_users_table.php
│   │   ├── 🐘 0001_01_01_000001_create_cache_table.php
│   │   ├── 🐘 0001_01_01_000002_create_jobs_table.php
│   │   ├── 🐘 2026_04_15_053200_create_academic_years_table.php
│   │   ├── 🐘 2026_04_15_053201_create_teachers_table.php
│   │   ├── 🐘 2026_04_15_053207_create_classrooms_table.php
│   │   ├── 🐘 2026_04_15_053214_create_students_table.php
│   │   ├── 🐘 2026_04_15_053224_create_subjects_table.php
│   │   ├── 🐘 2026_04_15_053230_create_student_classrooms_table.php
│   │   ├── 🐘 2026_04_15_053236_create_teacher_subjects_table.php
│   │   ├── 🐘 2026_04_15_054331_create_permission_tables.php
│   │   ├── 🐘 2026_04_15_071940_create_predicate_configs_table.php
│   │   ├── 🐘 2026_04_15_071950_create_assessment_components_table.php
│   │   ├── 🐘 2026_04_15_072006_create_student_assessments_table.php
│   │   ├── 🐘 2026_04_15_072013_create_report_cards_table.php
│   │   ├── 🐘 2026_04_15_072018_create_report_card_notes_table.php
│   │   ├── 🐘 2026_04_15_075800_create_payment_types_table.php
│   │   ├── 🐘 2026_04_15_075806_create_invoices_table.php
│   │   ├── 🐘 2026_04_15_075810_create_payments_table.php
│   │   ├── 🐘 2026_04_15_085323_create_school_settings_table.php
│   │   ├── 🐘 2026_04_15_085328_create_letter_types_table.php
│   │   ├── 🐘 2026_04_15_085335_create_letter_templates_table.php
│   │   ├── 🐘 2026_04_15_085345_create_letters_table.php
│   │   ├── 🐘 2026_04_15_085351_create_letter_recipients_table.php
│   │   ├── 🐘 2026_04_16_073255_add_grade_to_students_table.php
│   │   ├── 🐘 2026_04_16_073306_add_alumni_expires_at_to_users_table.php
│   │   ├── 🐘 2026_04_16_075124_add_type_to_teachers_table.php
│   │   ├── 🐘 2026_04_19_000001_update_report_cards_add_approval_flow.php
│   │   ├── 🐘 2026_04_20_014239_add_ki_to_assessment_components_table.php
│   │   ├── 🐘 2026_04_20_052844_create_notifications_table.php
│   │   ├── 🐘 2026_04_20_055320_add_receipt_code_to_invoices_table.php
│   │   ├── 🐘 2026_04_20_100000_create_payment_requests_table.php
│   │   ├── 🐘 2026_04_20_152045_add_avatar_to_users_table.php
│   │   ├── 🐘 2026_04_21_041323_make_tu_keuangan_id_nullable_in_payments_table.php
│   │   ├── 🐘 2026_04_22_071531_add_profile_fields_to_school_settings_table.php
│   │   ├── 🐘 2026_04_22_071542_create_extracurriculars_table.php
│   │   ├── 🐘 2026_04_22_071543_create_school_galleries_table.php
│   │   ├── 🐘 2026_04_24_000001_create_teacher_teaching_hours_table.php
│   │   ├── 🐘 2026_04_24_000002_create_teacher_attendances_table.php
│   │   ├── 🐘 2026_04_24_000003_create_teacher_honorariums_table.php
│   │   ├── 🐘 2026_04_24_000004_add_position_allowance_to_honorarium_tables.php
│   │   ├── 🐘 2026_04_24_023811_add_position_to_teachers_table.php
│   │   ├── 🐘 2026_04_24_100001_create_ppdb_settings_table.php
│   │   ├── 🐘 2026_04_24_100002_create_ppdb_registrations_table.php
│   │   ├── 🐘 2026_04_24_100003_alter_ppdb_registrations_add_fields.php
│   │   ├── 🐘 2026_06_02_000001_add_soft_deletes_to_ppdb_registrations_table.php
│   │   ├── 🐘 2026_06_02_000002_add_unique_to_student_classrooms_table.php
│   │   ├── 🐘 2026_06_03_000001_add_geolocation_to_school_settings_and_attendances.php
│   │   ├── 🐘 2026_06_04_000001_create_school_posts_table.php
│   │   ├── 🐘 2026_06_04_000002_add_hero_images_to_school_settings.php
│   │   ├── 🐘 2026_06_04_000003_create_school_hero_photos_table.php
│   │   ├── 🐘 2026_06_04_000004_add_family_fields_to_students.php
│   │   ├── 🐘 2026_06_04_000005_make_nisn_nullable_in_students.php
│   │   ├── 🐘 2026_06_04_143432_add_parent_phone_to_students_table.php
│   │   ├── 🐘 2026_06_04_154356_create_school_post_images_table.php
│   │   ├── 🐘 2026_06_04_200000_create_extracurricular_achievements_table.php
│   │   ├── 🐘 2026_06_04_200001_add_detail_fields_to_extracurriculars_table.php
│   │   ├── 🐘 2026_06_04_210000_create_extracurricular_photos_table.php
│   │   ├── 🐘 2026_06_05_000001_add_signature_to_users_table.php
│   │   ├── 🐘 2026_06_06_040314_add_student_id_to_ppdb_registrations_table.php
│   │   ├── 🐘 2026_06_06_044301_add_dp_fields_to_ppdb_settings.php
│   │   ├── 🐘 2026_06_06_044302_add_ppdb_registration_to_invoices.php
│   │   ├── 🐘 2026_06_07_044000_make_payment_type_nullable_on_invoices.php
│   │   ├── 🐘 2026_06_07_044005_drop_payment_type_from_ppdb_settings.php
│   │   └── 🐘 2026_06_09_154413_refactor_subjects_and_teacher_subjects.php
│   ├── 📁 seeders
│   │   ├── 🐘 AcademicYearSeeder.php
│   │   ├── 🐘 AssessmentComponentSeeder.php
│   │   ├── 🐘 ClassroomSeeder.php
│   │   ├── 🐘 DatabaseSeeder.php
│   │   ├── 🐘 ExtracurricularSeeder.php
│   │   ├── 🐘 LetterSeeder.php
│   │   ├── 🐘 PaymentSeeder.php
│   │   ├── 🐘 PredicateConfigSeeder.php
│   │   ├── 🐘 ReportCardSeeder.php
│   │   ├── 🐘 SchoolGallerySeeder.php
│   │   ├── 🐘 SchoolHeroPhotoSeeder.php
│   │   ├── 🐘 SchoolPostSeeder.php
│   │   ├── 🐘 SchoolSettingSeeder.php
│   │   ├── 🐘 StudentAssessmentSeeder.php
│   │   ├── 🐘 StudentSeeder.php
│   │   ├── 🐘 SubjectSeeder.php
│   │   ├── 🐘 TeacherSeeder.php
│   │   └── 🐘 UserSeeder.php
│   ├── ⚙️ .gitignore
│   └── 📄 database.sqlite
├── 📁 docs
│   ├── 📝 README.md
│   ├── 📝 akademik.md
│   ├── 📝 keuangan.md
│   ├── 📝 master-data.md
│   └── 📝 surat.md
├── 📁 public
│   ├── 📁 icons
│   │   ├── 🖼️ apple-touch-icon.png
│   │   ├── 🖼️ icon-128x128.png
│   │   ├── 🖼️ icon-144x144.png
│   │   ├── 🖼️ icon-152x152.png
│   │   ├── 🖼️ icon-192x192.png
│   │   ├── 🖼️ icon-384x384.png
│   │   ├── 🖼️ icon-512x512.png
│   │   ├── 🖼️ icon-72x72.png
│   │   └── 🖼️ icon-96x96.png
│   ├── ⚙️ .htaccess
│   ├── 📄 favicon.ico
│   ├── 🐘 index.php
│   └── 📄 robots.txt
├── 📁 resources
│   ├── 📁 css
│   │   └── 🎨 app.css
│   ├── 📁 js
│   │   ├── 📁 Components
│   │   │   ├── 📄 ApplicationLogo.vue
│   │   │   ├── 📄 BackButton.vue
│   │   │   ├── 📄 Checkbox.vue
│   │   │   ├── 📄 DangerButton.vue
│   │   │   ├── 📄 Dropdown.vue
│   │   │   ├── 📄 DropdownLink.vue
│   │   │   ├── 📄 FilterSelect.vue
│   │   │   ├── 📄 InputError.vue
│   │   │   ├── 📄 InputLabel.vue
│   │   │   ├── 📄 JsonLd.vue
│   │   │   ├── 📄 Modal.vue
│   │   │   ├── 📄 NavLink.vue
│   │   │   ├── 📄 PageLoading.vue
│   │   │   ├── 📄 Pagination.vue
│   │   │   ├── 📄 PrimaryButton.vue
│   │   │   ├── 📄 PublicHeader.vue
│   │   │   ├── 📄 PwaInstall.vue
│   │   │   ├── 📄 ResponsiveNavLink.vue
│   │   │   ├── 📄 SecondaryButton.vue
│   │   │   └── 📄 TextInput.vue
│   │   ├── 📁 Layouts
│   │   │   ├── 📄 AppLayout.vue
│   │   │   ├── 📄 AuthenticatedLayout.vue
│   │   │   └── 📄 GuestLayout.vue
│   │   ├── 📁 Pages
│   │   │   ├── 📁 Auth
│   │   │   │   ├── 📄 ConfirmPassword.vue
│   │   │   │   ├── 📄 ForgotPassword.vue
│   │   │   │   ├── 📄 Login.vue
│   │   │   │   ├── 📄 Register.vue
│   │   │   │   ├── 📄 ResetPassword.vue
│   │   │   │   └── 📄 VerifyEmail.vue
│   │   │   ├── 📁 Guru
│   │   │   │   ├── 📁 Assessment
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 Attendance
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 ReportCard
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   └── 📄 Dashboard.vue
│   │   │   ├── 📁 Honor
│   │   │   │   └── 📄 Verify.vue
│   │   │   ├── 📁 Kamad
│   │   │   │   ├── 📁 AcademicYear
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 Honorarium
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 Letter
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Verify.vue
│   │   │   │   ├── 📁 Ppdb
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 ReportCard
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 TeacherAttendance
│   │   │   │   │   └── 📄 Recap.vue
│   │   │   │   └── 📄 Dashboard.vue
│   │   │   ├── 📁 Keuangan
│   │   │   │   ├── 📁 Honorarium
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 Invoice
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 Payment
│   │   │   │   │   └── 📄 Receipt.vue
│   │   │   │   ├── 📁 PaymentType
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 PpdbInvoice
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   └── 📄 Dashboard.vue
│   │   │   ├── 📁 Operator
│   │   │   │   ├── 📁 AcademicYear
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 AssessmentComponent
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 Classroom
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 Extracurricular
│   │   │   │   │   ├── 📄 Create.vue
│   │   │   │   │   ├── 📄 Edit.vue
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 Letter
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 LetterTemplate
│   │   │   │   │   ├── 📄 Create.vue
│   │   │   │   │   ├── 📄 Edit.vue
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 LetterType
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 Ppdb
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 PredicateConfig
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 SchoolGallery
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 SchoolPost
│   │   │   │   │   ├── 📄 Create.vue
│   │   │   │   │   ├── 📄 Edit.vue
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 SchoolSetting
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 Staff
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 Student
│   │   │   │   │   ├── 📄 Create.vue
│   │   │   │   │   ├── 📄 Export.vue
│   │   │   │   │   ├── 📄 Import.vue
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 Subject
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 Teacher
│   │   │   │   │   ├── 📄 Create.vue
│   │   │   │   │   ├── 📄 Export.vue
│   │   │   │   │   ├── 📄 Import.vue
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 TeacherAttendance
│   │   │   │   │   └── 📄 Recap.vue
│   │   │   │   ├── 📁 TeachingHour
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   └── 📄 Dashboard.vue
│   │   │   ├── 📁 Profile
│   │   │   │   ├── 📁 Partials
│   │   │   │   │   ├── 📄 DeleteUserForm.vue
│   │   │   │   │   ├── 📄 UpdatePasswordForm.vue
│   │   │   │   │   └── 📄 UpdateProfileInformationForm.vue
│   │   │   │   └── 📄 Edit.vue
│   │   │   ├── 📁 Receipt
│   │   │   │   └── 📄 Verify.vue
│   │   │   ├── 📁 Siswa
│   │   │   │   ├── 📁 Invoice
│   │   │   │   │   └── 📄 Index.vue
│   │   │   │   ├── 📁 Letter
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   ├── 📁 Payment
│   │   │   │   │   └── 📄 Receipt.vue
│   │   │   │   ├── 📁 ReportCard
│   │   │   │   │   ├── 📄 Index.vue
│   │   │   │   │   └── 📄 Show.vue
│   │   │   │   └── 📄 Dashboard.vue
│   │   │   ├── 📄 Berita.vue
│   │   │   ├── 📄 BeritaDetail.vue
│   │   │   ├── 📄 Dashboard.vue
│   │   │   ├── 📄 Ekskul.vue
│   │   │   ├── 📄 EkskulDetail.vue
│   │   │   ├── 📄 Galeri.vue
│   │   │   ├── 📄 Ppdb.vue
│   │   │   ├── 📄 PpdbCheck.vue
│   │   │   ├── 📄 PpdbDaftar.vue
│   │   │   ├── 📄 Tentang.vue
│   │   │   └── 📄 Welcome.vue
│   │   ├── 📄 app.js
│   │   └── 📄 bootstrap.js
│   └── 📁 views
│       ├── 📁 pdf
│       │   ├── 🐘 honorarium_slip.blade.php
│       │   ├── 🐘 letter.blade.php
│       │   ├── 🐘 receipt.blade.php
│       │   └── 🐘 report_card.blade.php
│       └── 🐘 app.blade.php
├── 📁 routes
│   ├── 🐘 auth.php
│   ├── 🐘 console.php
│   └── 🐘 web.php
├── 📁 storage
│   ├── 📁 app
│   │   ├── 📁 private
│   │   │   ├── 📁 imports
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 public
│   │   │   ├── 📁 avatars
│   │   │   │   ├── 🖼️ JHfEcb2PrgPuXIb9ciXU06K6RYkPMGqflOUeGObK.jpg
│   │   │   │   ├── 🖼️ r2UnNAMxCTvkgWvVXCq476RO2he7Ac2ms1NDohF8.png
│   │   │   │   └── 🖼️ ytBXfITNspLmmLhAuKl3cEiGe0QbVvQh8xaTmqbx.jpg
│   │   │   ├── 📁 hero
│   │   │   │   ├── 🖼️ ekskul-0.jpg
│   │   │   │   ├── 🖼️ ekskul-1.jpg
│   │   │   │   ├── 🖼️ ekskul-2.jpg
│   │   │   │   ├── 🖼️ galeri-0.jpg
│   │   │   │   ├── 🖼️ galeri-1.jpg
│   │   │   │   ├── 🖼️ galeri-2.jpg
│   │   │   │   ├── 🖼️ tentang-0.jpg
│   │   │   │   ├── 🖼️ tentang-1.jpg
│   │   │   │   ├── 🖼️ tentang-2.jpg
│   │   │   │   ├── 🖼️ welcome-0.jpg
│   │   │   │   ├── 🖼️ welcome-1.jpg
│   │   │   │   └── 🖼️ welcome-2.jpg
│   │   │   ├── 📁 ppdb
│   │   │   │   ├── 📁 683c0d18-e9ac-4b19-b8b1-91b38f9e548f
│   │   │   │   │   ├── 🖼️ LJOgvfnFnZhNaULdvmAOnSzSPBauV5f1DZsKJLt6.jpg
│   │   │   │   │   ├── 🖼️ sypsSglKEZtHOodUbks58IE7fOdye6bXjAvKEqRh.jpg
│   │   │   │   │   └── 🖼️ x8Jw8JV3SVD4xZasANs9tOrpLoRKkxsPonji8iJQ.jpg
│   │   │   │   ├── 🖼️ 5TNZ9BUsXm0RWUcGkbOKjqdNyyUnfQDpxUlSdvLw.jpg
│   │   │   │   ├── 📕 FuZf0S6AiiGRX7pwB6z3h7mi5SjxpGVMET7efG5u.pdf
│   │   │   │   ├── 🖼️ IuVYjejJqjXKCnCEPTazJOYv7l3NW80iFz4hxT0t.jpg
│   │   │   │   ├── 🖼️ qHRy2cLr2K2Mq1BqALDJiXDDa3Q7P1UlxxJetNH5.jpg
│   │   │   │   ├── 📕 vW8T88DFeCeQ4aIQhiuaRoCvlA7JmIMSNudIcocr.pdf
│   │   │   │   └── 🖼️ z0FeL8ogXJaZmXoxU4UM2CmiRBrBssBGulGqzQAh.jpg
│   │   │   ├── 📁 school
│   │   │   │   ├── 📁 logo
│   │   │   │   │   ├── 🖼️ KkMuxjNlB93Ye6jrsAKKT5o7Jm0wVV8pHTXvFza2.png
│   │   │   │   │   ├── 🖼️ PFX5ozjrbm3Ag9lfe2YIje9EtrYPN89lACXI8JPT.png
│   │   │   │   │   └── 🖼️ je1axLhLfDvVMW2Qb8V63uLb7BzheQgoxYYUaRCy.jpg
│   │   │   │   └── 📁 stamp
│   │   │   │       └── 🖼️ WxOME7733ZeYS5ZPSYxkvSDntepfbnxhgIg9nzYF.jpg
│   │   │   ├── 📁 school-posts
│   │   │   ├── 📁 signatures
│   │   │   │   └── 🖼️ 3fPNOMdhUPyIVqOKf8SWzE8Q0Ljgy1dcbT9B445z.png
│   │   │   └── ⚙️ .gitignore
│   │   └── ⚙️ .gitignore
│   ├── 📁 framework
│   │   ├── 📁 sessions
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 testing
│   │   │   ├── 📁 disks
│   │   │   │   └── 📁 public
│   │   │   │       └── 📁 school
│   │   │   │           └── 📁 logo
│   │   │   │               └── 🖼️ ZH3jT2ymgC11MzEmykJqOOZRPYJAlWGtLcAfuJsy.png
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 views
│   │   │   ├── ⚙️ .gitignore
│   │   │   └── 🐘 795ea7b67a6562e6b31a67f883533896.php
│   │   └── ⚙️ .gitignore
│   └── 📁 logs
│       └── ⚙️ .gitignore
├── 📁 tests
│   ├── 📁 Feature
│   │   ├── 📁 Auth
│   │   │   ├── 🐘 AuthenticationTest.php
│   │   │   ├── 🐘 EmailVerificationTest.php
│   │   │   ├── 🐘 PasswordConfirmationTest.php
│   │   │   ├── 🐘 PasswordResetTest.php
│   │   │   ├── 🐘 PasswordUpdateTest.php
│   │   │   └── 🐘 RegistrationTest.php
│   │   ├── 📁 Http
│   │   │   └── 📁 Controllers
│   │   │       ├── 📁 Guru
│   │   │       │   ├── 🐘 AssessmentControllerTest.php
│   │   │       │   └── 🐘 ReportCardControllerTest.php
│   │   │       ├── 📁 Kamad
│   │   │       │   ├── 🐘 AcademicYearControllerTest.php
│   │   │       │   ├── 🐘 LetterControllerTest.php
│   │   │       │   └── 🐘 ReportCardControllerTest.php
│   │   │       ├── 📁 Keuangan
│   │   │       │   ├── 🐘 InvoiceControllerTest.php
│   │   │       │   ├── 🐘 PaymentControllerTest.php
│   │   │       │   └── 🐘 PaymentTypeControllerTest.php
│   │   │       ├── 📁 Operator
│   │   │       │   ├── 🐘 AcademicYearControllerTest.php
│   │   │       │   ├── 🐘 ClassroomControllerTest.php
│   │   │       │   ├── 🐘 LetterControllerTest.php
│   │   │       │   ├── 🐘 LetterTemplateControllerTest.php
│   │   │       │   ├── 🐘 LetterTypeControllerTest.php
│   │   │       │   ├── 🐘 StudentControllerTest.php
│   │   │       │   ├── 🐘 SubjectControllerTest.php
│   │   │       │   ├── 🐘 TeacherControllerTest.php
│   │   │       │   └── 🐘 TeacherSubjectControllerTest.php
│   │   │       └── 📁 Siswa
│   │   │           ├── 🐘 InvoiceControllerTest.php
│   │   │           ├── 🐘 LetterControllerTest.php
│   │   │           ├── 🐘 PaymentControllerTest.php
│   │   │           └── 🐘 ReportCardControllerTest.php
│   │   ├── 🐘 ExampleTest.php
│   │   └── 🐘 ProfileTest.php
│   ├── 📁 Unit
│   │   ├── 📁 Services
│   │   │   ├── 🐘 AcademicYearServiceTest.php
│   │   │   ├── 🐘 AssessmentComponentServiceTest.php
│   │   │   ├── 🐘 ClassroomServiceTest.php
│   │   │   ├── 🐘 InvoiceServiceTest.php
│   │   │   ├── 🐘 LetterServiceTest.php
│   │   │   ├── 🐘 LetterTemplateServiceTest.php
│   │   │   ├── 🐘 LetterTypeServiceTest.php
│   │   │   ├── 🐘 PaymentServiceTest.php
│   │   │   ├── 🐘 PaymentTypeServiceTest.php
│   │   │   ├── 🐘 PredicateConfigServiceTest.php
│   │   │   ├── 🐘 ReportCardServiceTest.php
│   │   │   ├── 🐘 SchoolSettingServiceTest.php
│   │   │   ├── 🐘 StudentAssessmentServiceTest.php
│   │   │   ├── 🐘 StudentServiceTest.php
│   │   │   ├── 🐘 SubjectServiceTest.php
│   │   │   ├── 🐘 TeacherServiceTest.php
│   │   │   └── 🐘 TeacherSubjectServiceTest.php
│   │   └── 🐘 ExampleTest.php
│   └── 🐘 TestCase.php
├── ⚙️ .editorconfig
├── ⚙️ .env.example
├── ⚙️ .gitattributes
├── ⚙️ .gitignore
├── ⚙️ .npmrc
├── 📝 CLAUDE.md
├── 📝 README.md
├── 📄 artisan
├── ⚙️ composer.json
├── ⚙️ jsconfig.json
├── ⚙️ package-lock.json
├── ⚙️ package.json
├── ⚙️ phpunit.xml
├── 📄 postcss.config.js
├── 📄 tailwind.config.js
└── 📄 vite.config.js
```

---
*Generated by FileTree Pro Extension*