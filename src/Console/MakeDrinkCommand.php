<?php
namespace Pdpaola\CoffeeMachine\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeDrinkCommand extends Command
{
    protected static $defaultName = 'app:order-drink';

    protected function configure()
    {
        $this->addArgument(
            'drink-type',
            InputArgument::REQUIRED,
            'The type of the drink. (Tea, Coffee or Chocolate)'
        );

        $this->addArgument(
            'money',
            InputArgument::REQUIRED,
            'The amount of money given by the user'
        );

        $this->addArgument(
            'sugars',
            InputArgument::OPTIONAL,
            'The number of sugars you want. (0, 1, 2)',
            0
        );

        $this->addOption(
            'extra-hot',
            'e',
            InputOption::VALUE_NONE,
            $description = 'If the user wants to make the drink extra hot'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderService = new OrderService();
        
        $drinkType = $input->getArgument('drink-type');
        $money = $input->getArgument('money');
        $sugars = $input->getArgument('sugars');
        $extraHot = $input->getOption('extra-hot');
        
        $orderService->placeOrder($output, $drinkType, $money, $sugars, $extraHot);
    }
}

class OrderService
{
    public function placeOrder(OutputInterface $output, $drinkType, $money, $sugars, $extraHot)
    {
        $this->validateInput($output, $drinkType, $money, $sugars);
        $this->makeDrink($output, $drinkType, $sugars, $extraHot);
        $this->storeOrder($drinkType, $sugars, $extraHot);
    }

    private function validateInput(OutputInterface $output, $drinkType, $money, $sugars)
    {
        if (!in_array($drinkType, ['tea', 'coffee', 'chocolate'])) {
            throw new \InvalidArgumentException('The drink type should be tea, coffee or chocolate.');
        }

        switch ($drinkType) {
            case 'tea':
                if ($money < 0.4) {
                    throw new \InvalidArgumentException('The tea costs 0.4.');
                }
                break;
            case 'coffee':
                if ($money < 0.5) {
                    throw new \InvalidArgumentException('The coffee costs 0.5.');
                }
                break;
            case 'chocolate':
                if ($money < 0.6) {
                    throw new \InvalidArgumentException('The chocolate costs 0.6.');
                }
                break;
        }

        if ($sugars < 0 || $sugars > 2) {
            throw new \InvalidArgumentException('The number of sugars should be between 0 and 2.');
        }
    }

    private function makeDrink(OutputInterface $output, $drinkType, $sugars, $extraHot)
    {
        $output->write("You have ordered a $drinkType");
        if ($extraHot) {
            $output->write(' extra hot');
        }

        if ($sugars > 0) {
            $output->write(" with $sugars sugars (stick included)");
        }
        $output->writeln('');

        $pdo = MysqlPdoClient::getPdo();

        $stmt= $pdo->prepare( 'INSERT INTO orders (drink_type, sugars, stick, extra_hot) VALUES (:drink_type, :sugars, :stick, :extra_hot)');
        $stmt->execute([
            'drink_type' => $drinkType,
            'sugars' => $sugars,
            'stick' => $stick ?: 0,
            'extra_hot' => $extraHot ?: 0,
        ]);
    }
}


        