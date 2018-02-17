# Keboola Component Skeleton Generator
This tool generates a skeleton for a new [Keboola Connection]() component. See the [Development Guide]() for more details.
You need [Docker]() to run this tool.

## Running
Run:

	docker run -i -t --volume=/path/to/repository/:/code/ quay.io/keboola/component-generator

The path `/path/to/repository/` is expected to contain an empty [Git]() repository.

Options:
`--setup-only` -- only run setup of Travis deployment
`--update` -- use to update existing repository, will ask about each file before copying

Pass options in the command line like this:

	docker run -i -t --volume=/path/to/repository/:/code/ quay.io/keboola/component-generator --setup-only

Setup of travis deployment does the following

- enable building of the repository
- build only if .travis.yml is present
- set `APP_IMAGE` variable
- set `KBC_DEVELOPERPORTAL_VENDOR` variable
- set `KBC_DEVELOPERPORTAL_APP` variable
- set `KBC_DEVELOPERPORTAL_USERNAME` variable
- set `KBC_DEVELOPERPORTAL_PASSWORD` variable
