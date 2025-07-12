<?php
namespace Deployer;

require 'recipe/symfony.php';

set('application', 'kadr-doc');
set('git_ssh_command', 'ssh -o StrictHostKeyChecking=no -i ~/.ssh/id_rsa');
// Или используйте ssh-agent
//set('forward_agent', true);
set('repository', 'git@github.com:flaaim/hr-docs.git');
set('php_version', '8.1');
set('bin/php', '/opt/php/8.1/bin/php');
set('writable_mode', 'chmod');

host('production')
    ->set('hostname', '31.31.198.114')
    ->set('port', 22)
    ->set('remote_user', 'u1656040')
    ->set('password', 'OHFvqDac7O8g3RB1')
    ->set('deploy_path', '~/www/kadr-doc.ru')
    ->set('public_path', '{{deploy_path}}/public')
    ->set('branch', 'master');

// Кастомная задача для symlinks
task('deploy:symlink', function () {
    // Стандартный symlink current -> releases/N
    run("cd {{deploy_path}} && ln -sfn {{release_path}} current");

    // Дополнительный symlink для public (если требуется)
    run("cd {{deploy_path}} && ln -sfn current/public public");
});

// Настройки Slim
set('shared_files', [
    'public/.htaccess',
    'public/robots.txt'
]);
set('shared_dirs', [
    'config/common/env',
    'public/uploads',
    'var/logs',
    'var/cache',
]);

set('writable_dirs', [
    'var/cache',
    'var/log',
    'public/uploads',
]);

set('bin/composer', '{{bin/php}} /var/www/u1656040/data/www/kadr-doc.ru/composer.phar');

set('composer_options', '--no-dev --optimize-autoloader --no-progress --no-interaction --no-scripts');

task('deploy:vendors', function () {
    run('cd {{release_path}} && {{bin/composer}} install {{composer_options}}');
});

// Дополнительная проверка
task('check:composer', function () {
    run('if [ ! -f {{deploy_path}}/composer.phar ]; then curl -sS https://getcomposer.org/installer | {{bin/php}} && mv composer.phar {{deploy_path}}/; fi');
});


before('deploy:vendors', 'check:composer');

task('deploy:symlink', function () {
    // Создаем стандартный симлинк current -> releases/N
    run("cd {{deploy_path}} && ln -sfn {{release_path}} current");

    // Дополнительно: симлинк для public (если нужно)
    run("cd {{deploy_path}} && ln -sfn current/public public_html");
});

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:vendors',
    'deploy:publish'
]);

after('deploy:failed', 'deploy:unlock');
