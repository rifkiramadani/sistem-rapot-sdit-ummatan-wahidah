export type User = {
    id: string; // Menggunakan ULID
    name: string;
    email: string;
    email_verified_at: string | null;

    // Relasi yang mungkin dimuat (eager-loaded)
    role?: Role;
};
