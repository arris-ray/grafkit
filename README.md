```
 ______     ______     ______     ______   __  __     __     ______
/\  ___\   /\  == \   /\  __ \   /\  ___\ /\ \/ /    /\ \   /\__  _\
\ \ \__ \  \ \  __<   \ \  __ \  \ \  __\ \ \  _"-.  \ \ \  \/_/\ \/
 \ \_____\  \ \_\ \_\  \ \_\ \_\  \ \_\    \ \_\ \_\  \ \_\    \ \_\
  \/_____/   \/_/ /_/   \/_/\/_/   \/_/     \/_/\/_/   \/_/     \/_/

A toolkit of Grafana utilities.

Version: 0.1.0-alpha

Usage:
  grafkit <subcommand> [--subcommand-options] [<arguments>]
  grafkit -h | --help
  grafkit --version

Options:
  -h --help  Display this help information.
  --version  Display version information.

Help:
  grafkit help [<subcommand>]

Available subcommands:
  batch_replace
  build
  cache
  find
  help
  replace
  shell
  subcommands
  version
```
# Pre-requisites
> ℹ️ **NOTE:** Currently, Grafkit only supports authorization to Grafana services via OIDC tokens.
- MacOS `security` - Keychain management for access to the Google Chrome Cookie Jar.
- [Google Chrome](https://www.google.com/chrome/) - Web browser to request an OIDC token for access to Grafana instances.
- [Docker](https://www.docker.com/) - Container runtime engine to run the Grafkit appplication.
- [Direnv](https://direnv.net/) - Command line shell extension to configure the Grafkit runtime environment.
- Command line shell, e.g. Bash - A command line environment to run Grafkit.

# Setup
You'll need to perform the following steps in the following order as a one-time setup process:
- Visit each of your Grafana instances in the Google Chrome browser to ensure you're browser has been cookied with a valid OIDC token.
- `cd grafkit` - Navigate to your Grafkit installation in a terminal.
- `cp .envrc.example .envrc` - Configure Grafkit with the path to your Google Chrome cookie jar.
- `cp config/config.yaml.example config/config.yaml` - Configure Grafkit to interact with your Grafana instance(s).
- `direnv allow` - Configure the terminal environment for running Grafkit.
- `grafkit build` - Build the Grafkit Docker image.
- `grafkit vendor` - Install Grafkit Composer dependencies.
- `grafkit cache` - Create a local cache of dashboards from all your configured Grafana instances.

# Commands
> Note: All string processing is restricted to exact matches only.

Once Grafkit is setup, you will generally run the following commands:

## Find
Search for arbitrary strings in dashboards across all your configured Grafana instances. For example:
```
> grafkit find my.example.metric

# DASHBOARDS
- grafana.production.example.com/d/xkstUwf4k/foo
- grafana.staging.example.com/d/zCRgiBNGz/foo-dev
- grafana.staging.example.com/d/0b00nreGz/foo-wip

# SUMMARY
- 5 string matches
- 3 dashboards
- 2 Grafana instances
```
## Replace
> ⚠️ **WARNING!** THIS COMMAND WILL COMMIT CHANGES TO YOUR CONFIGURED GRAFANA INSTANCES.
> 
> It is your responsibility to confirm the scope of changes are not broader than you intend.
> This tool will indiscriminately replace any matched text anywhere in the JSON structure of a Grafana dashboard.

Replace an arbitrary string with another one in dashboards across all your configured Grafana instances. For example:
```
> grafkit replace my.example.metric another.example.metric

# DASHBOARDS
- grafana.production.example.com/d/xkstUwf4k/foo
- grafana.staging.example.com/d/zCRgiBNGz/foo-dev
- grafana.staging.example.com/d/0b00nreGz/foo-wip

# SUMMARY
- 5 string matches
- 3 dashboards
- 2 Grafana instances

Are you sure you want to proceed? (y/N) y

# RESULTS
- ✅ grafana.production.example.com/d/xkstUwf4k/foo
- ✅ grafana.staging.example.com/d/zCRgiBNGz/foo-dev
- ✅ grafana.staging.example.com/d/0b00nreGz/foo-wip
```
## Cache
You can refresh your local cache of Grafana dashboards from all your configured Grafana instances by issuing the following command:
```
> grafkit cache
```
If you only want to refresh dashboards from a specific Grafana instance, provide its label name as an argument. For example:
```
> grafkit cache production
```
Dashboards are cached into the `/resources/cache/dashboard` directory.

## Shell
For debugging purposes, a command is available to easily shell into a Grafkit Docker container:
```
> grafkit shell
root@3028ffd068fd:/app#
```
## References
1. Github, [xmwx/bash-boilerplate](https://github.com/xwmx/bash-boilerplate) - Grafkit shell script boilerplate.
2. Github, [php-pds/skeleton](https://github.com/php-pds/skeleton) - Grafkit project structure.
3. PHP-FIG, [PHP Standards Recommendations](https://www.php-fig.org/psr/)