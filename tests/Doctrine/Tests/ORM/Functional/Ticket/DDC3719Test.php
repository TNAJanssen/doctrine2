<?php

namespace Doctrine\Tests\ORM\Functional\Ticket;


use Doctrine\Common\Collections\Criteria;
use Doctrine\Tests\Models\Company\CompanyFlexContract;
use Doctrine\Tests\Models\Company\CompanyManager;

/**
 * @author Jean Carlo Machado <contato@jeancarlomachado.com.br>
 */
class DDC3719Test extends \Doctrine\Tests\OrmFunctionalTestCase
{
    public function setUp()
    {
        $this->useModelSet('company');
        parent::setUp();
    }

    /**
     * @group DDC-3719
     */
    public function testCriteriaOnNotOwningSide()
    {
        $manager = new CompanyManager();
        $manager->setName('Gandalf');
        $manager->setSalary(666);
        $manager->setTitle('Boss');
        $manager->setDepartment('Marketing');
        $this->em->persist($manager);

        $contractA = new CompanyFlexContract();
        $contractA->markCompleted();
        $contractA->addManager($manager);
        $this->em->persist($contractA);

        $contractB = new CompanyFlexContract();
        $contractB->addManager($manager);
        $this->em->persist($contractB);

        $this->em->flush();
        $this->em->refresh($manager);

        $contracts = $manager->managedContracts;
        static::assertCount(2, $contracts);

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("completed", true));

        $completedContracts = $contracts->matching($criteria);
        static::assertCount(1, $completedContracts);
    }
}
