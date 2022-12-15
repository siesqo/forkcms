## SSH settings
set :client, "???"
set :host, "ssh???.webhosting.be"

## Production/Staging URL's
set :production_url, "https://production.???.be" # or www.
set :staging_url, "https://staging.???.be"

## Only set once
set :domain_name, "???.be"
set :repo_url, "git@bitbucket.org:siesqo/???.git"

### DO NOT EDIT BELOW ###
set :deploytag_utc, false
set :deploytag_time_format, "%Y%m%d-%H%M%S"
set :files_dir, %w(src/Frontend/Files)

## Gulp build our custom theme
require 'json'
namespace :deploy do
  package = JSON.parse(File.read('composer.json'))

  # compile and upload the assets, but only if it's a Fork CMS project
  after :updated, 'siesqo:assets:put' if package['name'] == 'forkcms/forkcms'
end

## Generate sitemap
namespace :sitemap do
  desc "Generate the sitemap.xml and sitemap files per provider."
  task :generate do
    on roles(:db) do
      execute "cd #{current_path} && bin/console sitemap:generate"
    end
  end
end

before "deploy:cleanup", "sitemap:generate"
after "deploy:rollback", "sitemap:generate"
