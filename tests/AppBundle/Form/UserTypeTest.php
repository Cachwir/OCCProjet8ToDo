<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserTypeTest
 * Cannot extend the TypeTestCase as its form factory mock bugs with "invalid_message" form type option.
 *
 * @package AppBundle\Form
 * @group unit
 */
class UserTypeTest extends WebTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'username' => "Unit_test_user_". uniqid(),
            'password' => [
                "first" => "password",
                "second" => "password",
            ],
            'email' => "unit-test-". uniqid() . "@gmx.com",
            'role' => "ROLE_ADMIN",
        );

        $client = static::createClient();

        $factory = $client->getKernel()->getContainer()->get("form.factory");

        $form = $factory->create(UserType::class);

        $user = new User();
        $user->setUsername($formData["username"]);
        $user->setPassword("password");
        $user->setEmail($formData["email"]);
        $user->setRole($formData["role"]);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        foreach ($form->getData() as $proprety => $value) {
            $method = "get". ucfirst($proprety);
            $this->assertEquals($user->$method(), $value);
        }

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
