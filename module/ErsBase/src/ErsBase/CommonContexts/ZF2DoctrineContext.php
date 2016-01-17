<?php

namespace ErsBase\CommonContexts;

use Behat\Behat\Context\BehatContext;
#use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
#use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Provides hooks for building and cleaning up a database schema with Doctrine.
 *
 * While building the schema it takes all the entity metadata known to Doctrine.
 *
 * @author Jakub Zalas <jakub@zalas.pl>
 * @editor Andreas Nitsche <andi@inbaz.org>
 */
class ZF2DoctrineContext extends BehatContext implements ServiceManagerAwareInterface
{
    /**
     * @var \Zend\ServiceManager $sm
     */
    private $sm = null;

    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @BeforeScenario
     *
     * @return null
     */
    public function buildSchema($event)
    {
        foreach ($this->getEntityManagers() as $entityManager) {
            $metadata = $this->getMetadata($entityManager);

            if (!empty($metadata)) {
                $tool = new SchemaTool($entityManager);
                $tool->dropSchema($metadata);
                $tool->createSchema($metadata);
            }
        }
    }

    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @AfterScenario
     *
     * @return null
     */
    public function closeDBALConnections($event)
    {
        /** @var EntityManager $entityManager */
        foreach ($this->getEntityManagers() as $entityManager) {
            $entityManager->clear();
        }

        /** @var Connection $connection */
        foreach ($this->getConnections() as $connection) {
            $connection->close();
        }
    }
    
    /**
     * @param \Zend\ServiceManager $sm
     *
     * @return null
     */
    public function setServiceManager($sm) {
        $this->sm = $sm;
    }
    
    /**
     * @return \Zend\ServiceManager
     */
    public function getServiceManager() {
        return $this->sm;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return array
     */
    protected function getMetadata(EntityManager $entityManager)
    {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        return $em->getMetadataFactory()->getAllMetadata();
        #return $entityManager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * @return array
     */
    protected function getEntityManagers()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        #return $this->kernel->getContainer()->get('doctrine')->getManagers();
    }

    /**
     * @return array
     */
    protected function getConnections()
    {
        error_log('this function is not doing anything');
        return null;
        #return $this->kernel->getContainer()->get('doctrine')->getConnections();
    }
}
