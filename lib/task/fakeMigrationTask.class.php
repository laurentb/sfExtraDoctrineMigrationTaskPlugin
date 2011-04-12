<?php

class fakeMigrationTask extends sfDoctrineBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('version', sfCommandArgument::OPTIONAL, 'Force migration version', null),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_OPTIONAL, 'The connection name', 'doctrine'),
    ));

    $this->aliases          = array('dcfm');
    $this->namespace        = 'doctrine';
    $this->name             = 'fake-migrate';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [doctrine:fake-migrate|INFO] task sets the current migration version in the database.
It will not perform any migration.
If no forced version is provided, it will automatically try to detect what it should be by counting the number of migration classes.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = Doctrine_Manager::getInstance()->connection();

    $number = $arguments['version'] === null ? $this->guessCurrentVersion() : $arguments['version'];

    $this->logSection($this->namespace.':'.$this->name, 'Setting current migration version to '.$number);

    $sql = 'DROP TABLE IF EXISTS `migration_version`;
    CREATE TABLE `migration_version` (
      `version` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    INSERT INTO `migration_version` VALUES (?);';

    $connection->execute($sql, array($number));
  }

  protected function guessCurrentVersion()
  {
    $config = $this->getCliConfig();

    $migrations = sfFinder::type('file')
      ->maxdepth(1)
      ->prune('.*')
      ->in($config['migrations_path']);

    return count($migrations);
  }
}
