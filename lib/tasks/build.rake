desc "Build binaries"
task :build do |task|

  # PHP
  system("composer config http-basic.nova.laravel.com #{ENV['NOVA_USER']} #{ENV['NOVA_LICENSE_KEY']}") or exit 1
  system('composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts --no-interaction') or exit 1
  system('cp .env.ci .env') or exit 1
  system('php artisan package:discover') or exit 1
  system('php artisan horizon:install') or exit 1
  system('php artisan nova:publish') or exit 1

  # NodeJS
  system('npm install') or exit 1
  system('npm run build') or exit 1
  FileUtils.rm_r('node_modules', :force => true)
  system('npm install --omit=dev') or exit 1
end
