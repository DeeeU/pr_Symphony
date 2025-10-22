<?php

namespace Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use AppBundle\Entity\User;

class UserTest extends TestCase
{
    public function testUserCanBeInstantiated()
    {
        $user = new User();

        // まずは基本的なテスト
        $this->assertInstanceOf(User::class, $user);

        // createdAt のテスト - null でないことを確認
        $this->assertNotNull($user->getCreatedAt());

        // createdAt が DateTime インスタンスであることを確認
        $this->assertInstanceOf(\DateTime::class, $user->getCreatedAt());

        // 初期状態ではメモが空であることを確認
        $this->assertCount(0, $user->getMemos());
    }

    public function testUserNameCanBeSetAndRetrieved()
    {
        $user = new User();
        $name = '山田太郎';

        $result = $user->setName($name);

        // Fluent interface のテスト（メソッドチェーン）
        $this->assertSame($user, $result);
        $this->assertEquals($name, $user->getName());
    }

    public function testUserEmailCanBeSetAndRetrieved()
    {
        $user = new User();
        $email = 'yamada@example.com';

        $user->setEmail($email);

        $this->assertEquals($email, $user->getEmail());
    }

    public function testUserPasswordCanBeSetAndRetrieved()
    {
        $user = new User();
        $password = 'hashed_password';

        $user->setPassword($password);

        $this->assertEquals($password, $user->getPassword());
    }

    // 権限関連のテスト

    public function testUserHasDefaultMemberRole()
    {
        $user = new User();

        // デフォルトでROLE_MEMBERを持つ
        $this->assertContains('ROLE_MEMBER', $user->getRoles());
        $this->assertTrue($user->hasRole('ROLE_MEMBER'));
    }

    public function testUserIsNotAdminByDefault()
    {
        $user = new User();

        // デフォルトではadminではない
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->hasRole('ROLE_ADMIN'));
    }

    public function testCanAddAdminRole()
    {
        $user = new User();

        $result = $user->addRole('ROLE_ADMIN');

        // Fluent interface のテスト
        $this->assertSame($user, $result);

        // ROLE_ADMINが追加されたことを確認
        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
        $this->assertTrue($user->isAdmin());

        // ROLE_MEMBERも維持されていることを確認
        $this->assertTrue($user->hasRole('ROLE_MEMBER'));
    }

    public function testCanSetMultipleRoles()
    {
        $user = new User();
        $roles = ['ROLE_MEMBER', 'ROLE_ADMIN'];

        $result = $user->setRoles($roles);

        // Fluent interface のテスト
        $this->assertSame($user, $result);

        $this->assertTrue($user->hasRole('ROLE_MEMBER'));
        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
        $this->assertTrue($user->isAdmin());
    }

    public function testCanRemoveRole()
    {
        $user = new User();
        $user->addRole('ROLE_ADMIN');

        // ROLE_ADMINを削除
        $result = $user->removeRole('ROLE_ADMIN');

        // Fluent interface のテスト
        $this->assertSame($user, $result);

        // ROLE_ADMINが削除されたことを確認
        $this->assertFalse($user->hasRole('ROLE_ADMIN'));
        $this->assertFalse($user->isAdmin());

        // ROLE_MEMBERは残っていることを確認
        $this->assertTrue($user->hasRole('ROLE_MEMBER'));
    }

    public function testGetRolesReturnsUniqueRoles()
    {
        $user = new User();
        $user->setRoles(['ROLE_MEMBER', 'ROLE_MEMBER', 'ROLE_ADMIN']);

        $roles = $user->getRoles();

        // 重複が除去されていることを確認
        $this->assertCount(2, $roles);
        $this->assertContains('ROLE_MEMBER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testAddRoleDoesNotCreateDuplicates()
    {
        $user = new User();

        // 同じロールを複数回追加
        $user->addRole('ROLE_ADMIN');
        $user->addRole('ROLE_ADMIN');

        $roles = $user->getRoles();

        // ROLE_ADMINは1つだけ
        $adminCount = count(array_filter($roles, function($role) {
            return $role === 'ROLE_ADMIN';
        }));

        $this->assertEquals(1, $adminCount);
    }

    public function testGetRolesAlwaysReturnsAtLeastOneRole()
    {
        $user = new User();
        $user->setRoles([]);

        $roles = $user->getRoles();

        // 空の配列を設定しても、最低1つのロール（ROLE_MEMBER）が返される
        $this->assertNotEmpty($roles);
        $this->assertContains('ROLE_MEMBER', $roles);
    }
}
