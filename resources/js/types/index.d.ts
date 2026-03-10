export interface User {
    id: number | string;
    full_name: string;
    email: string;
    email_verified_at?: string;
    roles?: string[];
    academic_title?: string;
    degree?: string;
    phone_number?: string;
    gender?: string;
    department?: string;
    photo_path?: string;
    teacher_external_id?: string;
}


export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash?: {
        success?: string;
        error?: string;
    };
};
