import AppLogoIcon from './app-logo-icon';
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
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">{getLogoText()}</span>
            </div>
        </>
    );
}
