<?php

declare(strict_types=1);

namespace App\Command;

use App\Controller\ControllerInterface;
use App\RouteDefinition\InvalidRouteDefinitionException;
use App\RouteDefinition\Specification\GeneratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpenApiV3GeneratorCommand extends Command
{
    private GeneratorInterface $generator;

    /**
     * @var ControllerInterface[]
     */
    private array $controllers;

    /**
     * @param GeneratorInterface $generator
     * @param ControllerInterface[] $controllers
     */
    public function __construct(GeneratorInterface $generator, array $controllers)
    {
        parent::__construct('app:openapi-v3-generator');

        $this->generator = $generator;
        $this->controllers = $controllers;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generates OpenApi v3.1.0 specification json file.')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'The destination where to save the file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');

        $routes = [];
        foreach ($this->controllers as $r) {
            $routes[] = $r::getDefinition();
        }

        try {
            $spec = $this
                ->generator
                ->generate("Example API", "", "", "", "", $routes);
        } catch (InvalidRouteDefinitionException $e) {
            $errors = json_encode($e->getErrors());
            if (!$errors) {
                throw new \Exception('Unable to get route validation errors');
            }

            $output->write($errors);

            return Command::FAILURE;
        }

        $content = json_encode($spec);

        if (!$content) {
            return Command::FAILURE;
        }

        $result = file_put_contents($filename, $content);

        if (!$result) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
