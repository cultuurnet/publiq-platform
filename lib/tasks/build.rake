desc "Build binaries"
task :build do |task|

  system("composer config http-basic.nova.laravel.com #{ENV['NOVA_USER']} #{ENV['NOVA_LICENSE_KEY']}") or exit 1
  system('composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction') or exit 1
end
