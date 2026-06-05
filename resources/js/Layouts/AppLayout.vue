<script setup>
import { ref, computed, watch, onMounted, onUnmounted, provide } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import PwaInstall from '@/Components/PwaInstall.vue';
import PageLoading from '@/Components/PageLoading.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRole = computed(() => page.props.auth.role || 'operator');

const sidebarOpen = ref(false);
const collapsed = ref(false);

const roleLabel = {
    operator:    'Operator',
    kamad:       'Kepala Madrasah',
    tu_keuangan: 'Staff Keuangan',
    guru:        'Guru',
    siswa:       'Wali Murid',
};

const navConfig = {
    operator: [
        { type: 'item',    label: 'Dashboard',           href: '/operator/dashboard',             icon: 'home' },
        { type: 'section', label: 'Master Data' },
        { type: 'item',    label: 'Tahun Ajaran',         href: '/operator/academic-years',        icon: 'calendar' },
        { type: 'item',    label: 'Guru',                 href: '/operator/teachers',              icon: 'academic-cap' },
        { type: 'item',    label: 'Kelas',                href: '/operator/classrooms',            icon: 'building' },
        { type: 'item',    label: 'Mata Pelajaran',       href: '/operator/subjects',              icon: 'book-open' },
        { type: 'item',    label: 'Siswa',                href: '/operator/students',              icon: 'users' },
        { type: 'section', label: 'Akademik' },
        { type: 'item',    label: 'Konfigurasi Predikat', href: '/operator/predicate-configs',     icon: 'star' },
        { type: 'item',    label: 'Komponen Nilai',       href: '/operator/assessment-components', icon: 'clipboard' },
        { type: 'section', label: 'Kepegawaian' },
        { type: 'item',    label: 'Jam Pelajaran',        href: '/operator/teaching-hours',        icon: 'clock' },
        { type: 'item',    label: 'Rekap Absensi Guru',  href: '/operator/teacher-attendances/recap', icon: 'chart-bar' },
        { type: 'section', label: 'PPDB' },
        { type: 'item',    label: 'PPDB',                 href: '/operator/ppdb',                  icon: 'user-plus' },
        { type: 'section', label: 'Persuratan' },
        { type: 'item',    label: 'Jenis Surat',          href: '/operator/letter-types',          icon: 'tag' },
        { type: 'item',    label: 'Template Surat',       href: '/operator/letter-templates',      icon: 'document' },
        { type: 'item',    label: 'Surat',                href: '/operator/letters',               icon: 'envelope' },
        { type: 'section', label: 'Konten Landing Page' },
        { type: 'item',    label: 'Galeri Sekolah',        href: '/operator/school-galleries',      icon: 'photo' },
        { type: 'item',    label: 'Ekstrakulikuler',        href: '/operator/extracurriculars',      icon: 'sparkles' },
        { type: 'item',    label: 'Berita & Pengumuman',    href: '/operator/school-posts',          icon: 'newspaper' },
        { type: 'section', label: 'Pengaturan' },
        { type: 'item',    label: 'Setting Sekolah',      href: '/operator/school-settings',       icon: 'cog' },
        { type: 'section', label: 'Akun' },
        { type: 'item',    label: 'Profil Saya',          href: '/profile',                        icon: 'user-circle' },
    ],
    kamad: [
        { type: 'item',    label: 'Dashboard',            href: '/kamad/dashboard',                icon: 'home' },
        { type: 'section', label: 'Persetujuan' },
        { type: 'item',    label: 'Tahun Ajaran',         href: '/kamad/academic-years',           icon: 'calendar' },
        { type: 'item',    label: 'Raport',               href: '/kamad/report-cards',             icon: 'clipboard' },
        { type: 'item',    label: 'Surat',                href: '/kamad/letters',                  icon: 'envelope' },
        { type: 'item',    label: 'Honor Guru',           href: '/kamad/honorariums',              icon: 'banknotes' },
        { type: 'item',    label: 'PPDB',                 href: '/kamad/ppdb',                     icon: 'user-plus' },
        { type: 'section', label: 'Monitoring' },
        { type: 'item',    label: 'Rekap Absensi Guru',  href: '/kamad/teacher-attendances/recap', icon: 'chart-bar' },
        { type: 'section', label: 'Akun' },
        { type: 'item',    label: 'Profil Saya',          href: '/profile',                        icon: 'user-circle' },
    ],
    tu_keuangan: [
        { type: 'item',    label: 'Dashboard',            href: '/keuangan/dashboard',             icon: 'home' },
        { type: 'section', label: 'Keuangan' },
        { type: 'item',    label: 'Jenis Tagihan',        href: '/keuangan/payment-types',         icon: 'tag' },
        { type: 'item',    label: 'Tagihan Siswa',        href: '/keuangan/invoices',              icon: 'credit-card' },
        { type: 'item',    label: 'Honor Guru',           href: '/keuangan/honorariums',           icon: 'banknotes' },
        { type: 'section', label: 'Akun' },
        { type: 'item',    label: 'Profil Saya',          href: '/profile',                        icon: 'user-circle' },
    ],
    guru: [
        { type: 'item',    label: 'Dashboard',            href: '/guru/dashboard',                 icon: 'home' },
        { type: 'section', label: 'Akademik' },
        { type: 'item',    label: 'Input Nilai',          href: '/guru/assessments',               icon: 'pencil' },
        { type: 'item',    label: 'Raport',               href: '/guru/report-cards',              icon: 'clipboard' },
        { type: 'section', label: 'Kepegawaian' },
        { type: 'item',    label: 'Absensi Saya',         href: '/guru/attendance',                icon: 'clock' },
        { type: 'section', label: 'Akun' },
        { type: 'item',    label: 'Profil Saya',          href: '/profile',                        icon: 'user-circle' },
    ],
    siswa: [
        { type: 'item',    label: 'Dashboard',            href: '/siswa/dashboard',                icon: 'home' },
        { type: 'section', label: 'Akademik' },
        { type: 'item',    label: 'Nilai & Raport',       href: '/siswa/report-cards',             icon: 'clipboard' },
        { type: 'section', label: 'Keuangan' },
        { type: 'item',    label: 'Tagihan',              href: '/siswa/invoices',                 icon: 'credit-card' },
        { type: 'section', label: 'Informasi' },
        { type: 'item',    label: 'Surat',                href: '/siswa/letters',                  icon: 'envelope' },
        { type: 'section', label: 'Akun' },
        { type: 'item',    label: 'Profil Saya',          href: '/profile',                        icon: 'user-circle' },
    ],
};

const navItems = computed(() => navConfig[userRole.value] || navConfig.operator);
const isActive = (href) => page.url.startsWith(href);

const userInitials = computed(() => {
    const name = user.value?.name || 'U';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const logout = () => router.post(route('logout'));

// ── Notifications ─────────────────────────────────────────────────────────────
const notifications  = computed(() => page.props.notifications ?? []);
const unreadCount    = computed(() => page.props.unreadCount ?? 0);
const notifOpen      = ref(false);

const typeIcon = {
    cash_request:    'cash',
    letter_request:  'envelope',
    letter_submitted:'envelope',
    letter_approved: 'check',
    letter_rejected: 'x',
    payment_recorded:'check',
};

const typeColor = {
    cash_request:    'bg-amber-100 text-amber-600',
    letter_request:  'bg-blue-100 text-blue-600',
    letter_submitted:'bg-violet-100 text-violet-600',
    letter_approved: 'bg-emerald-100 text-emerald-600',
    letter_rejected: 'bg-red-100 text-red-600',
    payment_recorded:'bg-emerald-100 text-emerald-600',
};

const markRead = async (id) => {
    await fetch(`/notifications/${id}/read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'Accept': 'application/json',
        },
    });
    router.reload({ only: ['notifications', 'unreadCount'], preserveScroll: true, preserveState: true });
};

const markAllRead = () => {
    router.patch(route('notifications.read-all'), {}, {
        preserveScroll: true,
        preserveState: true,
    });
};

// Close notif dropdown when clicking outside
const handleOutsideClick = (e) => {
    if (notifOpen.value && !e.target.closest('[data-notif-panel]')) {
        notifOpen.value = false;
    }
};

// ── Page navigation loading ───────────────────────────────────────────────────
const navigating = ref(false);
let navTimeout = null;

// Poll notifications every 30s so semua role dapat update real-time
let pollTimer = null;
const pollNotifications = () => {
    if (!document.hidden) {
        router.reload({ only: ['notifications', 'unreadCount'], preserveScroll: true, preserveState: true });
    }
};

onMounted(() => {
    document.addEventListener('click', handleOutsideClick);
    pollTimer = setInterval(pollNotifications, 30_000);

    // Tampilkan loading overlay saat navigasi Inertia (delay 120ms agar fast nav tidak flicker)
    router.on('start', () => {
        navTimeout = setTimeout(() => { navigating.value = true; }, 120);
    });
    router.on('finish', () => {
        clearTimeout(navTimeout);
        navigating.value = false;
    });
});
onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick);
    clearInterval(pollTimer);
    clearTimeout(navTimeout);
});

// ── Toast system ─────────────────────────────────────────────────────────────
const toasts = ref([]);
let toastSeq = 0;

const TOAST_DURATION = 4500;

const addToast = (message, type) => {
    const id = ++toastSeq;
    toasts.value.push({ id, message, type, leaving: false, entering: true });
    requestAnimationFrame(() => requestAnimationFrame(() => {
        const t = toasts.value.find(t => t.id === id);
        if (t) t.entering = false;
    }));
    setTimeout(() => dismissToast(id), TOAST_DURATION);
};

const dismissToast = (id) => {
    const toast = toasts.value.find(t => t.id === id);
    if (!toast) return;
    toast.leaving = true;
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, 250);
};

watch(() => page.props.flash, (flash) => {
    if (flash?.success) addToast(flash.success, 'success');
    if (flash?.error)   addToast(flash.error,   'error');
}, { immediate: true });

provide('addToast', addToast);

const iconPaths = {
    home:           'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    calendar:       'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    users:          'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    'academic-cap': 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
    building:       'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    'book-open':    'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
    star:           'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
    clipboard:      'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z',
    'credit-card':  'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
    tag:            'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L9.568 3zM6 6h.008v.008H6V6z',
    document:       'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    envelope:       'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    cog:            'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
    bell:           'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
    pencil:         'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
    'user-circle':  'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z',
    clock:          'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    banknotes:      'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    photo:          'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
    sparkles:       'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z',
    newspaper:      'M12 7.5h1.5m-1.5 3h1.5m-3 1.5h.008v.008H10.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM10.5 7.5h.008v.008H10.5V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM7.5 15h.008v.008H7.5V15zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375-3h.008v.008H7.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375-3h.008v.008H7.5V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3 9.75A.75.75 0 013.75 9h16.5a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 17.25v-7.5zM6.75 6.75h10.5v-1.5H6.75v1.5zM4.5 4.5h15v-1.5h-15v1.5z',
    'chart-bar':    'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
    logout:         'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75',
    menu:           'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5',
    chevron:        'M15.75 19.5L8.25 12l7.5-7.5',
};
</script>

<template>
    <Head>
        <meta name="robots" content="noindex, nofollow">
    </Head>

    <div class="flex h-screen overflow-hidden bg-slate-100 font-sans antialiased">

        <!-- ── Mobile overlay ─────────────────────────────────────────────────── -->
        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-30 bg-black/50 lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- ── SIDEBAR ──────────────────────────────────────────────────────────── -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex flex-col bg-[#0D1B2A] transition-transform duration-200',
                'lg:relative lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                collapsed   ? 'lg:w-[68px]'   : 'lg:w-[260px]',
                'w-[260px]',
            ]"
        >
            <!-- Logo -->
            <div
                :class="[
                    'flex items-center border-b border-white/10 px-4 py-5',
                    collapsed ? 'justify-center' : 'gap-3',
                ]"
            >
                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500 shadow-sm">
                    <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.building" />
                    </svg>
                </div>
                <div
                    :class="[
                        'overflow-hidden',
                        collapsed ? 'w-0 opacity-0' : 'w-full opacity-100',
                    ]"
                >
                    <p class="truncate text-sm font-semibold leading-tight text-white">MI / SD</p>
                    <p class="truncate text-xs text-white/40">Manajemen Sekolah</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-4 scrollbar-thin">
                <template v-for="item in navItems" :key="item.label || item.section">

                    <!-- Section label -->
                    <div
                        v-if="item.type === 'section'"
                        :class="[
                            'mb-1 mt-5 px-4 first:mt-2',
                            collapsed ? 'hidden' : '',
                        ]"
                    >
                        <p class="text-[10px] font-semibold uppercase text-white/30">
                            {{ item.label }}
                        </p>
                    </div>

                    <!-- Section divider when collapsed -->
                    <div
                        v-if="item.type === 'section' && collapsed"
                        class="mx-4 my-3 border-t border-white/10"
                    />

                    <!-- Nav item -->
                    <Link
                        v-if="item.type === 'item'"
                        :href="item.href"
                        :class="[
                            'group relative mx-2 mb-0.5 flex items-center rounded-lg transition-[background-color,color] duration-150',
                            collapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5',
                            isActive(item.href)
                                ? 'bg-emerald-500/15 text-emerald-400'
                                : 'text-white/50 hover:bg-white/5 hover:text-white/90',
                        ]"
                    >
                        <!-- Active bar -->
                        <span
                            v-if="isActive(item.href)"
                            class="absolute left-0 top-1/2 h-5 w-0.5 -translate-y-1/2 rounded-full bg-emerald-400"
                        />

                        <!-- Icon -->
                        <svg
                            class="size-5 shrink-0"
                            :class="isActive(item.href) ? 'text-emerald-400' : 'text-white/40 group-hover:text-white/70'"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths[item.icon] || iconPaths.document" />
                        </svg>

                        <!-- Label -->
                        <span
                            :class="[
                                'truncate text-sm font-medium',
                                collapsed ? 'hidden' : '',
                            ]"
                        >
                            {{ item.label }}
                        </span>

                        <!-- Tooltip (collapsed) -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full ml-3 hidden whitespace-nowrap rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg ring-1 ring-white/10 lg:group-hover:block"
                        >
                            {{ item.label }}
                        </div>
                    </Link>

                </template>
            </nav>

            <!-- User + logout -->
            <div class="border-t border-white/10 p-3">
                <div
                    :class="[
                        'mb-2 flex items-center rounded-lg px-2 py-2',
                        collapsed ? 'justify-center' : 'gap-3',
                    ]"
                >
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white overflow-hidden">
                        <img v-if="user?.avatar_url" :src="user.avatar_url" class="size-full object-cover" alt="" />
                        <span v-else>{{ userInitials }}</span>
                    </div>
                    <div
                        :class="[
                            'overflow-hidden',
                            collapsed ? 'w-0 opacity-0' : 'flex-1 opacity-100',
                        ]"
                    >
                        <p class="truncate text-sm font-semibold leading-tight text-white/90">{{ user?.name }}</p>
                        <p class="truncate text-xs text-white/40">{{ roleLabel[userRole] }}</p>
                    </div>
                </div>

                <button
                    aria-label="Keluar dari aplikasi"
                    @click="logout"
                    :class="[
                        'flex w-full items-center rounded-lg text-white/40 transition-[background-color,color] duration-150 hover:bg-white/5 hover:text-red-400',
                        collapsed ? 'justify-center px-0 py-2.5' : 'gap-3 px-3 py-2',
                    ]"
                >
                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.logout" />
                    </svg>
                    <span :class="['text-sm font-medium', collapsed ? 'hidden' : '']">Keluar</span>
                </button>
            </div>
        </aside>

        <!-- ── MAIN ─────────────────────────────────────────────────────────────── -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="flex h-14 shrink-0 items-center gap-3 border-b border-slate-200 bg-white px-4 shadow-sm">

                <!-- Mobile hamburger -->
                <button
                    aria-label="Buka menu navigasi"
                    class="flex size-9 items-center justify-center rounded-lg text-slate-500 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.menu" />
                    </svg>
                </button>

                <!-- Desktop collapse toggle -->
                <button
                    :aria-label="collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
                    class="hidden size-9 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600 lg:flex"
                    @click="collapsed = !collapsed"
                >
                    <svg
                        :class="['size-4', collapsed ? 'rotate-180' : '']"
                        fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.chevron" />
                    </svg>
                </button>

                <!-- Title slot -->
                <div class="flex flex-1 items-center gap-2">
                    <slot name="title">
                        <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
                    </slot>
                </div>

                <!-- Actions + notification bell + avatar -->
                <div class="flex items-center gap-2">
                    <slot name="actions" />

                    <!-- Bell -->
                    <div class="relative" data-notif-panel>
                        <button
                            aria-label="Notifikasi"
                            @click.stop="notifOpen = !notifOpen"
                            class="relative flex size-9 items-center justify-center rounded-lg text-slate-500 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.bell" />
                            </svg>
                            <span
                                v-if="unreadCount > 0"
                                class="absolute right-1.5 top-1.5 flex size-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold tabular-nums text-white"
                            >
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Dropdown panel -->
                        <Transition
                            enter-from-class="opacity-0 translate-y-1"
                            enter-active-class="transition-[transform,opacity] duration-150 ease-out"
                            leave-to-class="opacity-0 translate-y-1"
                            leave-active-class="transition-[transform,opacity] duration-150 ease-in"
                        >
                            <div
                                v-if="notifOpen"
                                class="absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                                @click.stop
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-slate-800">Notifikasi</h3>
                                        <span
                                            v-if="unreadCount > 0"
                                            class="inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-xs font-semibold text-red-700"
                                        >{{ unreadCount }}</span>
                                    </div>
                                    <button
                                        v-if="unreadCount > 0"
                                        @click="markAllRead"
                                        class="text-xs font-semibold text-emerald-600 hover:underline"
                                    >
                                        Tandai semua dibaca
                                    </button>
                                </div>

                                <!-- List -->
                                <div class="max-h-80 overflow-y-auto">
                                    <div
                                        v-if="notifications.length === 0"
                                        class="flex flex-col items-center justify-center py-10"
                                    >
                                        <svg class="mb-2 size-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.bell" />
                                        </svg>
                                        <p class="text-xs text-slate-400">Belum ada notifikasi</p>
                                    </div>

                                    <button
                                        v-for="notif in notifications"
                                        :key="notif.id"
                                        @click="markRead(notif.id)"
                                        :class="[
                                            'flex w-full items-start gap-3 px-4 py-3 text-left transition-[background-color] duration-150 hover:bg-slate-50',
                                            !notif.read_at ? 'bg-emerald-50/50' : '',
                                        ]"
                                    >
                                        <!-- Icon -->
                                        <div
                                            :class="['mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg text-xs', typeColor[notif.type] ?? 'bg-slate-100 text-slate-500']"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path v-if="typeIcon[notif.type] === 'cash'" stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                                <path v-else-if="typeIcon[notif.type] === 'check'" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                <path v-else-if="typeIcon[notif.type] === 'x'" stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                <path v-else stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.envelope" />
                                            </svg>
                                        </div>

                                        <!-- Text -->
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-1">
                                                <p class="text-xs font-semibold text-slate-800">{{ notif.title }}</p>
                                                <span
                                                    v-if="!notif.read_at"
                                                    class="mt-0.5 size-2 shrink-0 rounded-full bg-emerald-500"
                                                />
                                            </div>
                                            <p class="text-pretty mt-0.5 text-xs text-slate-500 line-clamp-2">{{ notif.message }}</p>
                                            <p class="mt-1 text-[11px] text-slate-400">{{ notif.created_at }}</p>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <div class="hidden size-8 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white overflow-hidden lg:flex">
                        <img v-if="user?.avatar_url" :src="user.avatar_url" class="size-full object-cover" alt="" />
                        <span v-else>{{ userInitials }}</span>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="min-h-0 flex-1 overflow-y-auto bg-slate-100 p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>

    <PwaInstall />

    <!-- Navigation loading overlay -->
    <PageLoading :show="navigating" overlay variant="spinner" text="Memuat..." />

    <!-- ── Toast container ──────────────────────────────────────────────────── -->
    <div
        class="fixed top-4 left-4 right-4 z-50 flex flex-col gap-2 pt-[env(safe-area-inset-top)] sm:top-5 sm:left-auto sm:right-5 sm:w-80"
        aria-live="polite"
        aria-atomic="false"
    >
        <div
            v-for="toast in toasts"
            :key="toast.id"
            :class="[
                'flex w-full flex-col overflow-hidden rounded-xl border bg-white shadow-lg',
                'transition-[transform,opacity] duration-200 ease-out',
                (toast.leaving || toast.entering) ? 'translate-x-4 opacity-0' : 'translate-x-0 opacity-100',
                toast.type === 'success' ? 'border-emerald-100' : 'border-red-100',
            ]"
            role="alert"
        >
            <div class="flex items-start gap-3 px-4 py-3.5">
                <!-- Icon -->
                <svg
                    v-if="toast.type === 'success'"
                    class="mt-0.5 size-4 shrink-0 text-emerald-500"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg
                    v-else
                    class="mt-0.5 size-4 shrink-0 text-red-500"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>

                <!-- Message -->
                <p class="text-pretty flex-1 text-sm text-slate-700">{{ toast.message }}</p>

                <!-- Close -->
                <button
                    type="button"
                    aria-label="Tutup notifikasi"
                    @click="dismissToast(toast.id)"
                    class="shrink-0 text-slate-300 transition-[color] duration-150 hover:text-slate-500"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Progress bar -->
            <div class="px-4 pb-3">
                <div class="h-0.5 overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="toast-progress h-full rounded-full"
                        :class="toast.type === 'success' ? 'bg-emerald-400' : 'bg-red-400'"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.08) transparent;
}
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.08);
    border-radius: 2px;
}

@keyframes toast-shrink {
    from { transform: scaleX(1); }
    to   { transform: scaleX(0); }
}
.toast-progress {
    transform-origin: left;
    animation: toast-shrink 4.5s linear forwards;
}
@media (prefers-reduced-motion: reduce) {
    .toast-progress { animation: none; }
}
</style>
