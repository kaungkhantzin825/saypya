/**
 * Shared types for the Inertia (public + student) surface.
 *
 * These mirror the JSON shapes produced by the controllers. Laravel resources are
 * not used yet, so keep them in sync when a controller's payload changes.
 */

export type Role = 'student' | 'lecturer' | 'admin';
export type UserStatus = 'pending' | 'active' | 'inactive';
export type PaymentStatus = 'pending' | 'completed' | 'failed' | 'refunded';
export type CourseLevel = 'beginner' | 'intermediate' | 'advanced' | 'all_levels';

export interface User {
    id: number;
    name: string;
    email: string;
    role: Role;
    status: UserStatus;
    avatar_url: string;
    bio?: string | null;
    phone?: string | null;
    country?: string | null;
    is_admin: boolean;
    is_lecturer: boolean;
    is_super_admin: boolean;
    created_at?: string;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    image_url?: string | null;
    courses_count?: number;
}

export interface InstructorSummary {
    id: number;
    name: string;
    avatar_url: string;
    bio?: string | null;
    courses_count?: number;
    students_count?: number;
    /** Only present on the Home page's "top instructors" payload. */
    average_rating?: number;
}

export interface Course {
    id: number;
    title: string;
    slug: string;
    description?: string | null;
    short_description?: string | null;
    thumbnail_url: string;
    preview_video_url?: string | null;
    price: number | string;
    discount_price?: number | string | null;
    current_price: number | string;
    discount_percentage: number;
    level: CourseLevel | string;
    status: string;
    language?: string | null;
    duration_hours?: number | null;
    is_featured: boolean;
    requirements?: string[] | null;
    what_you_learn?: string[] | null;
    category?: Category | null;
    instructor?: InstructorSummary | null;
    average_rating?: number;
    total_reviews?: number;
    total_students?: number;
    total_lessons?: number;
    total_duration?: number;
    is_enrolled?: boolean;
    is_in_wishlist?: boolean;
    /** Eager-loaded relations (present only on the pages that need them). */
    sections?: Section[];
    lessons?: Lesson[];
    reviews?: Review[];
    discussions?: Discussion[];
    exams?: Exam[];
    enrollments?: Enrollment[];
    /** `withCount('enrollments')` alias used by the admin listings. */
    enrollments_count?: number;
    created_at?: string;
    updated_at?: string;
}

export interface Lesson {
    id: number;
    section_id: number;
    title: string;
    description?: string | null;
    /** `video` | `text` | `quiz` | `assignment`. */
    type?: string | null;
    /** Body copy for text lessons. */
    content?: string | null;
    /** Raw value as stored: a YouTube URL, a full URL, or a relative storage path. */
    video_url?: string | null;
    /** Resolved playable URL (relative paths are prefixed with /storage). */
    video_url_full?: string | null;
    /** Present only when the lesson video is a YouTube link. */
    youtube_embed_url?: string | null;
    /** mm:ss, from the `formatted_duration` accessor. */
    formatted_duration?: string | null;
    /** Duration in **seconds** (despite the name). */
    video_duration?: number | null;
    sort_order: number;
    /** Column is `is_preview` — whether the lesson is a free preview. */
    is_preview?: boolean;
    is_completed?: boolean;
    progress?: LessonProgress | null;
}

export interface Section {
    id: number;
    course_id: number;
    title: string;
    description?: string | null;
    sort_order: number;
    lessons: Lesson[];
    lessons_count?: number;
}

export interface LessonProgress {
    id: number;
    lesson_id: number;
    user_id: number;
    is_completed: boolean;
    watch_time?: number | null;
    completed_at?: string | null;
}

export interface Enrollment {
    id: number;
    user_id: number;
    course_id: number;
    user?: User;
    course?: Course;
    price_paid: number | string;
    payment_status: PaymentStatus;
    payment_method?: string | null;
    progress_percentage: number;
    enrolled_at?: string | null;
    completed_at?: string | null;
    last_accessed_at?: string | null;
    created_at?: string;
}

export type ExamQuestionType = 'multiple_choice' | 'true_false' | 'essay';

export interface ExamQuestion {
    id: number;
    exam_id: number;
    question: string;
    type: ExamQuestionType;
    options?: string[] | null;
    correct_answer?: string | null;
    points: number;
    order: number;
}

export interface Exam {
    id: number;
    course_id: number;
    course?: Course;
    creator?: User | null;
    title: string;
    description?: string | null;
    duration_minutes?: number | null;
    passing_score: number;
    max_attempts: number;
    show_results: boolean;
    show_correct_answers: boolean;
    is_published: boolean;
    questions?: ExamQuestion[];
    /** `withCount('questions')` alias used by the admin listing. */
    questions_count?: number;
    /** `withCount('attempts')` alias used by the admin listing. */
    attempts_count?: number;
    total_points?: number;
    created_at?: string;
}

export interface ExamAnswer {
    id: number;
    attempt_id: number;
    question_id: number;
    question?: ExamQuestion;
    answer: string | null;
    is_correct: boolean | null;
    points_earned: number | null;
    feedback?: string | null;
}

export type AttemptStatus = 'in_progress' | 'submitted' | 'graded';

export interface ExamAttempt {
    id: number;
    exam_id: number;
    user_id: number;
    exam?: Exam;
    score: number | null;
    total_points: number;
    passed: boolean | null;
    status: AttemptStatus;
    started_at?: string | null;
    submitted_at?: string | null;
    answers?: ExamAnswer[];
    user?: User;
    /** Appended accessor — divide-by-zero guarded server-side. */
    percentage?: number;
}

export interface BlogPost {
    id: number;
    title: string;
    slug: string;
    excerpt?: string | null;
    content: string;
    /** Accessor alias of `featured_image_url`. */
    image_url?: string | null;
    featured_image_url?: string | null;
    is_published?: boolean;
    status?: string;
    published_at?: string | null;
    author?: User | null;
    category?: string | null;
    /** Estimated minutes, from the `reading_time` accessor. */
    reading_time?: number;
    views_count?: number;
}

export interface Review {
    id: number;
    course_id: number;
    user_id: number;
    rating: number;
    comment?: string | null;
    is_approved: boolean;
    user?: User | null;
    course?: Course | null;
    created_at?: string;
}

export type ContactMessageStatus = 'new' | 'read' | 'replied';

export interface ContactMessage {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    subject?: string | null;
    message: string;
    status: ContactMessageStatus;
    admin_reply?: string | null;
    replied_at?: string | null;
    created_at?: string;
}

/** ---- Reports ---------------------------------------------------------- */

export interface ReportStats {
    total_users: number;
    total_courses: number;
    total_enrollments: number;
    total_revenue: number;
}

export interface MonthlyRevenue {
    labels: string[];
    data: number[];
}

export interface TopCourse {
    id: number;
    title: string;
    enrollments_count: number;
    revenue: number;
    instructor?: { id: number; name: string } | null;
}

export interface TopInstructor {
    id: number;
    name: string;
    courses_count: number;
    students_count: number;
    revenue: number;
}

export interface CategoryStat {
    id: number;
    name: string;
    courses_count: number;
    students_count: number;
    revenue: number;
    avg_rating: number;
}

/** ---- Site settings ---------------------------------------------------- */

/** `text` | `textarea` | `number` | `image` — drives which control renders. */
export type SiteSettingType = 'text' | 'textarea' | 'number' | 'image';

export interface SiteSetting {
    id: number;
    key: string;
    value: string | null;
    type: SiteSettingType | string;
    group: string;
    label: string;
    description?: string | null;
    /** Resolved server-side; only set for `image` settings. */
    image_url?: string | null;
}

export interface SiteSettingGroup {
    key: string;
    /** Group key with underscores replaced by spaces. */
    label: string;
    settings: SiteSetting[];
}

export interface Comment {
    id: number;
    course_id: number;
    user_id: number;
    parent_id?: number | null;
    /** Column is `comment`, not `body`. */
    comment: string;
    is_approved: boolean;
    user?: User | null;
    replies?: Comment[];
    created_at?: string;
}

export interface Discussion {
    id: number;
    course_id: number;
    user_id: number;
    title: string;
    /** Column is `content`, not `body`. */
    content: string;
    is_resolved: boolean;
    user?: User | null;
    replies?: DiscussionReply[];
    replies_count?: number;
    created_at?: string;
}

export interface DiscussionReply {
    id: number;
    discussion_id: number;
    user_id: number;
    /** Column is `content`, not `body`. */
    content: string;
    user?: User | null;
    created_at?: string;
}

export interface HeroSlide {
    id: number;
    title?: string | null;
    subtitle?: string | null;
    /** From the `image_url` accessor (resolves relative storage paths). */
    image_url: string;
    /** Column is `button_text`, not `cta_text`. */
    button_text?: string | null;
    /** Column is `button_link`, not `cta_url`. */
    button_link?: string | null;
    sort_order: number;
    is_active: boolean;
}

/** Laravel paginator payload. */
export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
}

export interface FlashMessages {
    success?: string | null;
    error?: string | null;
    info?: string | null;
    warning?: string | null;
}

export interface AppContext {
    name: string;
    locale: string;
    locales: Record<string, string>;
    registrationEnabled: boolean;
}

export interface AuthContext {
    user: User | null;
    wishlistCount: number;
}

/** Props shared with every Inertia page by HandleInertiaRequests. */
export interface SharedPageProps {
    app: AppContext;
    auth: AuthContext;
    flash: FlashMessages;
    /**
     * Inertia's `usePage<T>()` requires T to satisfy its own `PageProps`, which
     * carries an index signature. Page-specific props still narrow correctly —
     * declared keys win over the index signature.
     */
    [key: string]: unknown;
}
