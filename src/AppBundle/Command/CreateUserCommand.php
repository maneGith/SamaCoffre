<?php
// src/AppBundle/Command/CreateUserCommand.php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CreateUserCommand extends Command
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        //Update the value of the private entityManager variable through injection
        $this->entityManager = $entityManager;

        parent::__construct();
    }
    
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:create-user')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
                
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user.')
            ->addArgument('role', InputArgument::REQUIRED, 'The role of the user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->entityManager;
        
        $output->writeln([
          'User Creator',
          '============',
          '',

          ]);

         $user= new User();
         $user->setEmail($input->getArgument('email'));
         $user->setPassword($input->getArgument('password'));
                 $user->setRoles(array($input->getArgument('role')));
                 $user->setEnabled(1);
        //Persist and flush
        $em->persist($user);
        $em->flush();
         
      


        // retrieve the argument value using getArgument()
        $output->writeln('Email: '.$input->getArgument('email'));
        $output->writeln('Password: '.$input->getArgument('password'));
        $output->writeln('Role: '.$input->getArgument('role'));

        $output->writeln('User successfully generated!');
          return 0;
    }
}