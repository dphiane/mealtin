<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserEntityTest extends KernelTestCase
{
    public function getEntity():User
    {
        return (new User())
            ->setEmail('dphiane@yahoo.fr')
            ->setFirstname('dominique')
            ->setLastname('phiane')
            ->setPassword('123456')
            ->setTelephone('0760423143');
    }
    
    public function assertHasErrors(User $user,int $number=0){
        self::bootKernel();
        $container = self::getContainer();
        $errors= $container->get('validator')->validate($user);
        $messages=[];
        /** @var ConstraintViolation $error */
        foreach($errors as $error){
            $messages[]= $error->getPropertyPath() . '=>' . $error->getMessage();
        }
        $this->assertCount($number,$errors,implode(', ', $messages));
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->getEntity(),0);
    }

    public function testInvalidEntityEmail(){
        $user=$this->getEntity()->setEmail('dphiane@');
        $this->assertHasErrors($user,1);
    }

    public function testNotBlankProperty(){
        $user= new User();
        $this->assertHasErrors($user,5);
    }

    public function testMinLengthPassword(){
        $user = $this->getEntity()->setPassword('1234');
        $this->assertHasErrors($user,1);
    }

    public function testUniqueEmail(){
        $user = $this->getEntity()->setEmail('eugene14@maillet.fr');
        $this->assertHasErrors($user,1);
    }
}
