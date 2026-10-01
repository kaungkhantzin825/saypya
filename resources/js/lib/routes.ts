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
} as const;
