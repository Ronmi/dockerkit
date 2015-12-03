<?php

namespace Fruit\DockerKit\Helper;

trait User
{
    /**
     * Be aware when using pipe or redirection.
     * Passing 'some_command > some_file' to sudo() will run some_command with sudo,
     * and redirect output to some_file with current user.
     * @return Dockerfile
     */
    public function sudo($cmd, $user = '', array $args = null)
    {
        if ($user == '') {
            $user = 'root';
        }
        if (!is_array($args)) {
            $args = array();
        }
        $args = implode(' ', array_map(function ($val) {
            return escapeshellarg($val);
        }, $args));

        return $this->shell(sprintf(
            'sudo -u %s %s -- %s',
            $user,
            $args,
            str_replace(
                "\n",
                "\\\n",
                $cmd
            )
        ));
    }

    /**
     * @return Dockerfile
     */
    public function addGroup($group, $gid)
    {
        return $this->shellAs(sprintf('addgroup --gid %d %s', $gid, $group), 'root');
    }

    /**
     * @return Dockerfile
     */
    public function addUser($user, $uid, $gid, $home = '', $gecos = '')
    {
        if (!$gecos) {
            $gecos = ',,,,,';
        }
        $cmd = sprintf(
            'adduser --uid %d --gid %d --disabled-password --gecos %s ',
            $uid,
            $gid,
            $gecos
        );
        if ($home === '') {
            $home = '/home/' . $user;
        }
        $homeStr = '--home ' . $home . ' ';
        if ($home === null) {
            $homeStr = '--no-create-home ';
        }
        $cmd .= $homeStr . $user;
        return $this->shellAs($cmd, 'root');
    }

    /**
     * @return Dockerfile
     */
    public function addSudoer($user)
    {
        return $this
            ->uStart('root')
            ->shell('mkdir -p /etc/sudoers.d')
            ->textfile("$user ALL=(ALL:ALL) NOPASSWD: ALL", "/etc/sudoers.d/$user")
            ->uEnd();
    }
}
