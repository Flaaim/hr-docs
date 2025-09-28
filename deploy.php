<?php
namespace Deployer;

require 'recipe/symfony.php';

set('application', 'kd-docs');
set('git_ssh_command', 'ssh -o StrictHostKeyChecking=no -i ~/.ssh/id_rsa');
set('repository', 'git@github.com:flaaim/hr-docs.git');
set('php_version', '8.1');
set('bin/php', '/opt/php/8.1/bin/php');
set('writable_mode', 'chmod');

host('production')
    ->set('hostname', '31.31.198.114')
    ->set('port', 22)
    ->set('remote_user', 'u1656040')
    ->set('deploy_path', '~/www/kd-docs.ru')
    ->set('public_path', '{{deploy_path}}/public')
    ->set('branch', 'master');

set('shared_files', [
    'public/.htaccess',
    'public/robots.txt',
]);

set('shared_dirs', [
    'config/common/env',
    'public/uploads',
    'var',
]);

set('writable_dirs', [
    'var/log',
    'var/cache',
    'public/uploads',
]);

// Updated composer path to use deploy_path
set('bin/composer', '{{bin/php}} {{deploy_path}}/composer.phar');
set('composer_options', '--no-dev --optimize-autoloader --no-progress --no-interaction --no-scripts');

// Improved composer check task
task('check:composer', function () {
    if (!test('[ -f {{deploy_path}}/composer.phar ]')) {
        run('cd {{deploy_path}} && curl -sS https://getcomposer.org/installer | {{bin/php}}');
        run('cd {{deploy_path}} && mv composer.phar {{deploy_path}}/composer.phar');
    }
});

task('deploy:vendors', function () {
    run('cd {{release_path}} && {{bin/composer}} install {{composer_options}}');
});

task('deploy:symlink', function () {
    run("cd {{deploy_path}} && ln -sfn {{release_path}} current");
    run("cd {{deploy_path}} && ln -sfn current/public public_html");
});

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:vendors',
    'deploy:publish'
]);

before('deploy:vendors', 'check:composer');
after('deploy:failed', 'deploy:unlock');
