import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const { props } = usePage();

    // Get current school academic year information
    const currentSchoolAcademicYear = props.currentSchoolAcademicYear as {
        id: string;
        academic_year: {
            name: string;
        };
    } | null;

    // Determine what text to show
    const getLogoText = () => {
        if (currentSchoolAcademicYear?.academic_year?.name) {
            return currentSchoolAcademicYear.academic_year.name;
        }
        return 'SDIT Ummatan Wahidah';
    };

    return (
        // Menggunakan flexbox untuk mengatur layout agar logo dan teks sejajar
        <div className="flex items-center">

            {/* Logo SDIT Ummatan Wahidah */}
            {/* Atur ukuran logo agar terlihat proporsional (misalnya width 32px atau kelas w-8) */}
            <img
                src="/assets/logo/sdit_ummatan_wahidah_logo.png"
                alt="Logo SDIT Ummatan Wahidah"
                className="h-8 w-8 object-contain" // Contoh: tinggi 8, lebar 8, jaga rasio
            />

            {/* Menghapus AppLogoIcon jika sudah menggunakan logo gambar */}

            {/* Teks Logo */}
            <div className="ml-2 grid flex-1 text-left text-sm"> {/* Tambah margin-left 2 (ml-2) */}
                {/* Teks utama (Nama Sekolah) */}
                <span className="mb-0.5 truncate leading-tight font-bold">
                    SDIT Ummatan Wahidah
                </span>
                {/* Teks kedua (Tahun Ajaran) diletakkan di bawahnya, jika perlu */}
                <span className="truncate leading-tight font-semibold text-gray-600 dark:text-gray-400">
                    {getLogoText()}
                </span>
            </div>
        </div>
    );
}
