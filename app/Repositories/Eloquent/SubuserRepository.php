<?php

namespace Pterodactyl\Repositories\Eloquent;

use Pterodactyl\Models\Subuser;
use Illuminate\Support\Collection;
use Pterodactyl\Exceptions\Repository\RecordNotFoundException;
use Pterodactyl\Contracts\Repository\SubuserRepositoryInterface;

class SubuserRepository extends EloquentRepository implements SubuserRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Subuser::class;
    }

    /**
     * Returns the subusers for the given server instance with the associated user
     * and permission relationships pre-loaded.
     *
     * @param int $server
     * @return \Illuminate\Support\Collection
     */
    public function getSubusersForServer(int $server): Collection
    {
        return $this->getBuilder()
            ->with('user', 'permissions')
            ->where('server_id', $server)
            ->get()
            ->toBase();
    }

    /**
     * Return a subuser with the associated server relationship.
     *
     * @param \Pterodactyl\Models\Subuser $subuser
     * @param bool $refresh
     * @return \Pterodactyl\Models\Subuser
     */
    public function loadServerAndUserRelations(Subuser $subuser, bool $refresh = false): Subuser
    {
        if (! $subuser->relationLoaded('server') || $refresh) {
            $subuser->load('server');
        }

        if (! $subuser->relationLoaded('user') || $refresh) {
            $subuser->load('user');
        }

        return $subuser;
    }

    /**
     * Return a subuser with the associated permissions relationship.
     *
     * @param \Pterodactyl\Models\Subuser $subuser
     * @param bool $refresh
     * @return \Pterodactyl\Models\Subuser
     */
    public function getWithPermissions(Subuser $subuser, bool $refresh = false): Subuser
    {
        if (! $subuser->relationLoaded('permissions') || $refresh) {
            $subuser->load('permissions');
        }

        if (! $subuser->relationLoaded('user') || $refresh) {
            $subuser->load('user');
        }

        return $subuser;
    }

    /**
     * Return a subuser and associated permissions given a user_id and server_id.
     *
     * @param int $user
     * @param int $server
     * @return \Pterodactyl\Models\Subuser
     *
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithPermissionsUsingUserAndServer(int $user, int $server): Subuser
    {
        $instance = $this->getBuilder()->with('permissions')->where([
            ['user_id', '=', $user],
            ['server_id', '=', $server],
        ])->first();

        if (is_null($instance)) {
            throw new RecordNotFoundException;
        }

        return $instance;
    }
}
