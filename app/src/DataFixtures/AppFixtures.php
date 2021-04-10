<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Employee;
use App\Entity\Product;
use App\Entity\Provider;
use App\Entity\StockByCompany;
use App\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Safe\DateTime;

use function assert;
use function count;
use function rand;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $company1 = new Company();
        $company1->setName('Plastik SA');
        $company1->setBalance(rand(50, 250));
        $company1->setCountry('France');
        $manager->persist($company1);

        $company2 = new Company();
        $company2->setName('Magiq SAS');
        $company2->setBalance(rand(50, 250));
        $company2->setCountry('France');
        $manager->persist($company2);

        $company3 = new Company();
        $company3->setName('YouRock!');
        $company3->setBalance(rand(50, 250));
        $company3->setCountry('France');
        $manager->persist($company3);

        $customer1 = new Customer();
        $customer1->setAddress('5 chemin du puit');
        $customer1->setCountry('France');
        $customer1->setName('Michel Dupont');
        $manager->persist($customer1);

        $customer2 = new Customer();
        $customer2->setAddress('25 allée des pins');
        $customer2->setCountry('France');
        $customer2->setName('Philippe Levague');
        $manager->persist($customer2);

        $customer3 = new Customer();
        $customer3->setAddress("Derrière l'église");
        $customer3->setCountry('France');
        $customer3->setName('Auguste Leblanc');
        $manager->persist($customer3);

        $employee1 = new Employee();
        $employee1->setName('Maurice Durant');
        $employee1->setBirthday(new DateTime('2000-01-15'));
        $employee1->setFirstDayInTheCompany(new DateTime('2020-05-12'));
        $employee1->setCountry('Belgique');
        $manager->persist($employee1);

        $employee2 = new Employee();
        $employee2->setName('Sonia King');
        $employee2->setBirthday(new DateTime('1990-03-19'));
        $employee2->setFirstDayInTheCompany(new DateTime('2015-12-05'));
        $employee2->setCountry('Angleterre');
        $manager->persist($employee2);

        $employee2 = new Employee();
        $employee2->setName('Victoria Conte');
        $employee2->setBirthday(new DateTime('1978-05-05'));
        $employee2->setFirstDayInTheCompany(new DateTime('2011-09-01'));
        $employee2->setCountry('Italie');
        $manager->persist($employee2);

        $product1 = new Product();
        $product1->setName('Pistolet à colle');
        $product1->setPrice(15);
        $product1->setTax(.2);
        $manager->persist($product1);

        $product2 = new Product();
        $product2->setName('Scie sauteuse');
        $product2->setPrice(95);
        $product2->setTax(.2);
        $manager->persist($product2);

        $product3 = new Product();
        $product3->setName('Tondeuse');
        $product3->setPrice(125);
        $product3->setTax(.2);
        $manager->persist($product3);

        $provider1 = new Provider();
        $provider1->setAddress('12 on the street');
        $provider1->setCountry('Angleterre');
        $provider1->setName('Amazon UK');
        $manager->persist($provider1);

        $provider2 = new Provider();
        $provider2->setAddress('Somewhere in USA');
        $provider2->setCountry('USA');
        $provider2->setName('Google');
        $manager->persist($provider2);

        $provider3 = new Provider();
        $provider3->setAddress('On the moon');
        $provider3->setCountry('On the moon');
        $provider3->setName('Elon Musk');
        $manager->persist($provider3);

        $companys = [$company1, $company2, $company3];
        $products = [$product1, $product2, $product3];
        $customers = [$customer1, $customer2, $customer3];
        $providers = [$provider1, $provider2, $provider3];
        foreach ($companys as $company) {
            assert($company instanceof Company);
            foreach ($products as $product) {
                /** @var Product $product */
                $stockByCompany = new StockByCompany();
                $stockByCompany->setCompany($company);
                $stockByCompany->setProduct($product);
                $stockByCompany->setStock(rand(1, 200));
                $manager->persist($stockByCompany);
            }

            for ($i = 0; $i < rand(5, 20); $i++) {
                $transaction = new Transaction();
                if (rand(0, 1) === 1) { //choose between a sale or a purchase
                    $transaction->setCustomer($customers[rand(0, count($customers) - 1)]);
                } else {
                    $transaction->setProvider($providers[rand(0, count($providers) - 1)]);
                }

                $transaction->setProduct($products[rand(0, count($customers) - 1)]);
                $transaction->setQuantity(rand(1, 75));
                $transaction->setCompany($company);
                $manager->persist($transaction);
            }
        }

        $manager->flush();
    }
}
