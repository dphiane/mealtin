<?php

namespace App\Tests\Repository;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    
    public function testCount(){
        self::bootKernel();
        $container = static::getContainer();
        $users = $container->get(UserRepository::class)->count([]);
        $this->assertEquals(2,$users);
    }

    public function testFindByEmail(){
        self::bootKernel();
        $container = static::getContainer();
        $user = $container->get(UserRepository::class)->findOneByEmail('eugene14@maillet.fr');
        $this->assertEquals('eugene14@maillet.fr',$user->getEmail());
    }

    public function testFindById(){
        self::bootKernel();
        $container = static::getContainer();
        $user = $container->get(UserRepository::class)->findOneById('14');
        $this->assertEquals('eugene14@maillet.fr',$user->getEmail());
    }
}
