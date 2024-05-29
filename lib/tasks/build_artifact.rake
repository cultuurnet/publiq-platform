desc "Create a debian package from the binaries."
task :build_artifact do |task|

  calver_version = ENV['PIPELINE_VERSION'].nil? ? Time.now.strftime("%Y.%m.%d.%H%M%S") : ENV['PIPELINE_VERSION']
  git_short_ref  = `git rev-parse --short HEAD`.strip
  version        = ENV['ARTIFACT_VERSION'].nil? ? "#{calver_version}+sha.#{git_short_ref}" : ENV['ARTIFACT_VERSION']
  artifact_name  = 'platform-api'
  vendor         = 'publiq VZW'
  maintainer     = 'Infra publiq <infra@publiq.be>'
  license        = 'Apache-2.0'
  description    = 'publiq platform API'
  source         = 'https://github.com/cultuurnet/publiq-platform'
  build_url      = ENV['JOB_DISPLAY_URL'].nil? ? "" : ENV['JOB_DISPLAY_URL']

  FileUtils.mkdir_p('pkg')

  FileUtils.rm(Dir.glob('.env*'))
  FileUtils.touch('.env')

  system("fpm -s dir -t deb -n #{artifact_name} -v #{version} -a all -p pkg \
    -x '.git*' -x pkg -x lib -x Rakefile -x Gemfile -x Gemfile.lock \
    -x .bundle -x Jenkinsfile -x vendor/bundle -x Makefile -x auth.json \
    -x docker-compose.yml \
    --prefix /var/www/platform-api \
    --config-files /var/www/platform-api/.env \
    --config-files /var/www/platform-api/nova_users.php \
    --deb-user www-data --deb-group www-data \
    --before-remove lib/tasks/prerm \
    --description '#{description}' --url '#{source}' --vendor '#{vendor}' \
    --license '#{license}' -m '#{maintainer}' \
    --deb-field 'Pipeline-Version: #{calver_version}' \
    --deb-field 'Git-Ref: #{git_short_ref}' \
    --deb-field 'Build-Url: #{build_url}' \
    ."
  ) or exit 1
end
