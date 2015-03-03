<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 3/2/2015
 * Time: 9:01 AM
 */

namespace Tlt\ProfileBundle\Form\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword
{
    /**
     * @SecurityAssert\UserPassword(
     *     message = "Valoare gresita pentru parola curenta."
     * )
     */
    protected $oldPassword;

    /**
     * @Assert\Length(
     *     min = 6,
     *     minMessage = "Parola trebuie sa contina cel putin 6 caractere."
     * )
     */
    protected $newPassword;

    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword()
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;

        return $this;
    }
}