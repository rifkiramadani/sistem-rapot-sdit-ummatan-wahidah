import { usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';

/**
 * Get the current user's role
 */
export function useUserRole() {
    const { props } = usePage<SharedData>();
    return props.auth.user?.role?.name || null;
}

/**
 * Check if the current user has a specific role
 */
export function hasRole(roleName: string): boolean {
    const currentRole = useUserRole();
    return currentRole === roleName;
}

/**
 * Check if the current user is a superadmin
 */
export function isSuperadmin(): boolean {
    return hasRole('superadmin');
}

/**
 * Check if the current user is an admin
 */
export function isAdmin(): boolean {
    return hasRole('admin');
}

/**
 * Check if the current user is a principal
 */
export function isPrincipal(): boolean {
    return hasRole('principal');
}

/**
 * Check if the current user is a teacher
 */
export function isTeacher(): boolean {
    return hasRole('teacher');
}

/**
 * Check if the current user has any management role (superadmin, admin, principal)
 */
export function isManagement(): boolean {
    return isSuperadmin() || isAdmin() || isPrincipal();
}

/**
 * Check if the current user can access master data
 */
export function canAccessMasterData(): boolean {
    return isManagement();
}

/**
 * Check if the current user can access classroom data
 */
export function canAccessClassroomData(): boolean {
    return isManagement() || isTeacher();
}

/**
 * Check if the current user can create/edit master data
 */
export function canManageMasterData(): boolean {
    return isManagement();
}

/**
 * Check if the current user can delete master data
 */
export function canDeleteMasterData(): boolean {
    return isManagement();
}