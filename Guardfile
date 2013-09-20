# A sample Guardfile
# More info at https://github.com/guard/guard#readme
# gem uninstall guard-phpunit && gem install guard-phpunit2

guard 'phpunit2', :cli => '--colors', :tests_path => 'tests' do
  watch(%r{^.+Test\.php$})

  watch(%r{Sinergia/(.+)/(.+).php}) {|m| "tests/#{m[2]}Test.php"}
end
