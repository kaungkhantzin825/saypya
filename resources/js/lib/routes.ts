/**
 * Central URL builder for every route the Inertia (public + student) surface uses.
 *
 * Deliberately hand-written rather than pulling in Ziggy: the set of routes this
 * surface touches is small and fixed, and this keeps the frontend build free of an
 * extra runtime dependency. Keep these in sync with `routes/web.php`.
 */
export const routes = {
    // --- Public ---------------------------------------------------------
    home: () => '/',
    search: (q?: string) => (q ? `/search?q=${encodeURIComponent(q)}` : '/search'),
    instructors: (id: number | string) => `/instructors/${id}`,
    blog: () => '/blog',
    blogPost: (slug: string) => `/blog/${slug}`,

    about: () => '/about',
    team: () => '/team',
    partners: () => '/partners',
    contact: () => '/contact',
    contactSubmit: () => '/contact',
    help: () => '/help',
    privacy: () => '/privacy',
    terms: () => '/terms',

    switchLanguage: (locale: string) => `/language/${locale}`,

    // --- Courses --------------------------------------------------------
    courses: (params?: Record<string, string | number | undefined>) => {
        if (!params) return '/courses';
        const query = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== '' && value !== null) {
                query.append(key, String(value));
            }
        });
        const qs = query.toString();
        return qs ? `/courses?${qs}` : '/courses';
    },
    course: (slug: string) => `/courses/${slug}`,
    checkout: (slug: string) => `/courses/${slug}/checkout`,
    enroll: (courseId: number | string) => `/courses/${courseId}/enroll`,
    learn: (slug: string) => `/courses/${slug}/learn`,
    learnLesson: (slug: string, lessonId: number | string) => `/courses/${slug}/learn/${lessonId}`,
    toggleWishlist: (courseId: number | string) => `/courses/${courseId}/wishlist`,

    // --- Categories -----------------------------------------------------
    categories: () => '/categories',
    category: (slug: string) => `/categories/${slug}`,

    // --- Auth -----------------------------------------------------------
    login: () => '/login',
    register: () => '/register',
    logout: () => '/logout',
    verifyEmailSent: () => '/verify-email-sent',
    verifyEmail: (token: string) => `/verify-email/${token}`,
    resendVerification: () => '/resend-verification',
    forgotPassword: () => '/forgot-password',
    passwordLinkSent: () => '/password-link-sent',
    resetPassword: () => '/reset-password',
    resetPasswordToken: (token: string) => `/reset-password/${token}`,

    // --- Account --------------------------------------------------------
    profile: () => '/profile',
    profileUpdate: () => '/profile',
    dashboard: () => '/dashboard',
    myCourses: () => '/my/courses',
    myWishlist: () => '/my/wishlist',
    myCertificates: () => '/my/certificates',
    myProgress: () => '/my/progress',

    // --- Lessons --------------------------------------------------------
    lessonProgress: (lessonId: number | string) => `/lessons/${lessonId}/progress`,
    lessonComplete: (lessonId: number | string) => `/lessons/${lessonId}/complete`,
    lessonIncomplete: (lessonId: number | string) => `/lessons/${lessonId}/incomplete`,
    lessonUncomplete: (lessonId: number | string) => `/lessons/${lessonId}/uncomplete`,

    // --- Reviews & comments ---------------------------------------------
    storeReview: (courseId: number | string) => `/courses/${courseId}/reviews`,
    updateReview: (reviewId: number | string) => `/reviews/${reviewId}`,
    destroyReview: (reviewId: number | string) => `/reviews/${reviewId}`,
    storeComment: (courseId: number | string) => `/courses/${courseId}/comments`,
    updateComment: (commentId: number | string) => `/comments/${commentId}`,
    destroyComment: (commentId: number | string) => `/comments/${commentId}`,

    // --- Exams ----------------------------------------------------------
    courseExams: (courseId: number | string) => `/courses/${courseId}/exams`,
    startExam: (examId: number | string) => `/exams/${examId}/start`,
    submitExam: (attemptId: number | string) => `/exam-attempts/${attemptId}/submit`,
    examResult: (attemptId: number | string) => `/exam-attempts/${attemptId}/result`,
    myExams: () => '/my/exams',

    // --- Discussions ----------------------------------------------------
    discussions: (courseId: number | string) => `/courses/${courseId}/discussions`,
    discussion: (courseId: number | string, id: number | string) =>
        `/courses/${courseId}/discussions/${id}`,
    discussionReply: (courseId: number | string, id: number | string) =>
        `/courses/${courseId}/discussions/${id}/replies`,
    discussionResolve: (courseId: number | string, id: number | string) =>
        `/courses/${courseId}/discussions/${id}/resolve`,

    // --- Admin panel ----------------------------------------------------
    // NOTE: `migrated: true` entries render Inertia pages and are safe for <Link>.
    // The rest are still Blade and MUST be navigated with a plain <a href>, or
    // Inertia will throw "All Inertia requests must receive a valid Inertia response".
    admin: {
        dashboard: () => '/admin/dashboard',
        users: (params?: Record<string, string | number | undefined>) =>
            withQuery('/admin/users', params),
        userCreate: () => '/admin/users/create',
        userStore: () => '/admin/users',
        user: (id: number | string) => `/admin/users/${id}`,
        userEdit: (id: number | string) => `/admin/users/${id}/edit`,
        userUpdate: (id: number | string) => `/admin/users/${id}`,
        userDestroy: (id: number | string) => `/admin/users/${id}`,
        userToggleStatus: (id: number | string) => `/admin/users/${id}/toggle-status`,
        userApprove: (id: number | string) => `/admin/users/${id}/approve`,
        userReject: (id: number | string) => `/admin/users/${id}/reject`,
        createLecturer: () => '/admin/create-lecturer',
        toggleRegistration: () => '/admin/toggle-registration',
        reports: () => '/admin/reports',
        settings: () => '/admin/settings',
        settingsUpdate: () => '/admin/settings',
        // Courses — index is Inertia; the rest are still Blade and must use <a>.
        courses: (params?: Record<string, string | number | undefined>) =>
            withQuery('/admin/courses', params),
        courseCreate: () => '/admin/courses/create',
        courseStore: () => '/admin/courses',
        courseUpdate: (id: number | string) => `/admin/courses/${id}`,
        course: (id: number | string) => `/admin/courses/${id}`,
        courseEdit: (id: number | string) => `/admin/courses/${id}/edit`,
        courseContent: (id: number | string) => `/admin/courses/${id}/content`,
        courseApprove: (id: number | string) => `/admin/courses/${id}/approve`,
        courseArchive: (id: number | string) => `/admin/courses/${id}/archive`,
        courseFeature: (id: number | string) => `/admin/courses/${id}/feature`,
        courseDestroy: (id: number | string) => `/admin/courses/${id}`,
        // Course curriculum (sections & lessons) — all Inertia.
        courseSectionsStore: (courseId: number | string) => `/admin/courses/${courseId}/sections`,
        courseSectionUpdate: (courseId: number | string, sectionId: number | string) =>
            `/admin/courses/${courseId}/sections/${sectionId}`,
        courseSectionDestroy: (courseId: number | string, sectionId: number | string) =>
            `/admin/courses/${courseId}/sections/${sectionId}`,
        courseLessonsStore: (courseId: number | string, sectionId: number | string) =>
            `/admin/courses/${courseId}/sections/${sectionId}/lessons`,
        courseLessonUpdate: (
            courseId: number | string,
            sectionId: number | string,
            lessonId: number | string,
        ) => `/admin/courses/${courseId}/sections/${sectionId}/lessons/${lessonId}`,
        courseLessonDestroy: (
            courseId: number | string,
            sectionId: number | string,
            lessonId: number | string,
        ) => `/admin/courses/${courseId}/sections/${sectionId}/lessons/${lessonId}`,
        // Categories — index + form are Inertia.
        categories: () => '/admin/categories',
        categoryCreate: () => '/admin/categories/create',
        categoryEdit: (id: number | string) => `/admin/categories/${id}/edit`,
        categoryStore: () => '/admin/categories',
        categoryUpdate: (id: number | string) => `/admin/categories/${id}`,
        categoryDestroy: (id: number | string) => `/admin/categories/${id}`,
        // Hero slides — index + form are Inertia.
        heroSlides: () => '/admin/hero-slides',
        heroSlideCreate: () => '/admin/hero-slides/create',
        heroSlideEdit: (id: number | string) => `/admin/hero-slides/${id}/edit`,
        heroSlideStore: () => '/admin/hero-slides',
        heroSlideUpdate: (id: number | string) => `/admin/hero-slides/${id}`,
        heroSlideDestroy: (id: number | string) => `/admin/hero-slides/${id}`,
        heroSlideToggle: (id: number | string) => `/admin/hero-slides/${id}/toggle`,
        // Enrollments — index is Inertia.
        enrollments: (params?: Record<string, string | number | undefined>) =>
            withQuery('/admin/enrollments', params),
        enrollmentApprove: (id: number | string) => `/admin/enrollments/${id}/approve`,
        enrollmentReject: (id: number | string) => `/admin/enrollments/${id}/reject`,
        enrollmentRefund: (id: number | string) => `/admin/enrollments/${id}/refund`,
        // Reviews — index + edit are Inertia.
        reviews: (params?: Record<string, string | number | undefined>) =>
            withQuery('/admin/reviews', params),
        reviewEdit: (id: number | string) => `/admin/reviews/${id}/edit`,
        reviewUpdate: (id: number | string) => `/admin/reviews/${id}`,
        reviewApprove: (id: number | string) => `/admin/reviews/${id}/approve`,
        reviewDestroy: (id: number | string) => `/admin/reviews/${id}`,
        // Contact messages — index + show are Inertia.
        contactMessages: (params?: Record<string, string | number | undefined>) =>
            withQuery('/admin/contact-messages', params),
        contactMessage: (id: number | string) => `/admin/contact-messages/${id}`,
        contactMessageReply: (id: number | string) => `/admin/contact-messages/${id}/reply`,
        contactMessageDestroy: (id: number | string) => `/admin/contact-messages/${id}`,
        contactMessageBulkDelete: () => '/admin/contact-messages/bulk-delete',
        // Blog — index + form are Inertia.
        blog: (params?: Record<string, string | number | undefined>) =>
            withQuery('/admin/blog', params),
        blogCreate: () => '/admin/blog/create',
        blogStore: () => '/admin/blog',
        blogEdit: (id: number | string) => `/admin/blog/${id}/edit`,
        blogUpdate: (id: number | string) => `/admin/blog/${id}`,
        blogDestroy: (id: number | string) => `/admin/blog/${id}`,
        // Exams — index, form, results and grade are all Inertia.
        exams: (params?: Record<string, string | number | undefined>) =>
            withQuery('/admin/exams', params),
        examCreate: () => '/admin/exams/create',
        examStore: () => '/admin/exams',
        examEdit: (id: number | string) => `/admin/exams/${id}/edit`,
        examUpdate: (id: number | string) => `/admin/exams/${id}`,
        examResults: (id: number | string) => `/admin/exams/${id}/results`,
        examDestroy: (id: number | string) => `/admin/exams/${id}`,
        examQuestionStore: (examId: number | string) => `/admin/exams/${examId}/questions`,
        examQuestionUpdate: (examId: number | string, questionId: number | string) =>
            `/admin/exams/${examId}/questions/${questionId}`,
        examQuestionDestroy: (examId: number | string, questionId: number | string) =>
            `/admin/exams/${examId}/questions/${questionId}`,
        examGrade: (attemptId: number | string) => `/admin/exam-attempts/${attemptId}/grade`,
        examSubmitGrade: (attemptId: number | string) => `/admin/exam-attempts/${attemptId}/grade`,
        /** Student-facing result view — still Blade, so open it as a plain <a>. */
        examResultView: (attemptId: number | string) => `/exam-attempts/${attemptId}/result`,
    },

    // --- Instructor panel (still Blade) ---------------------------------
    instructor: {
        dashboard: () => '/instructor/dashboard',
        courses: () => '/instructor/courses',
        students: () => '/instructor/students',
        exams: () => '/instructor/exams',
        reviews: () => '/instructor/reviews',
        earnings: () => '/instructor/earnings',
        analytics: () => '/instructor/analytics',
    },
} as const;

/** Append a query string, dropping empty/undefined values. */
function withQuery(path: string, params?: Record<string, string | number | undefined>): string {
    if (!params) return path;

    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== '' && value !== null) {
            query.append(key, String(value));
        }
    });

    const qs = query.toString();
    return qs ? `${path}?${qs}` : path;
}
