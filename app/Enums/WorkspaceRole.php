<?php

namespace App\Enums;

enum WorkspaceRole: string
{
    case Admin = 'admin';
    case Member = 'member';
    case Owner = 'owner'; // backward-compatible alias for admin

    public function label(): string
    {
        return match ($this) {
            self::Owner, self::Admin => 'Admin',
            self::Member => 'User',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Owner, self::Admin => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::Member => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    public function canManageWorkspace(): bool
    {
        return $this->isAdmin();
    }

    public function canManageMembers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Tidak ada hierarki antar user: semua bisa membuat brief,
     * saling menugaskan tugas lapangan/edit, checklist, dan review.
     */
    public function canCreateBrief(): bool
    {
        return true;
    }

    public function canAssignTasks(): bool
    {
        return true;
    }

    public function canApproveContent(): bool
    {
        return true;
    }

    public function canPublish(): bool
    {
        return true;
    }
}
