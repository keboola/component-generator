# Keboola Component Skeleton Generator
This tool generates a skeleton for a new [Keboola Connection]() component. See the [Development Guide]() for more details.
You need [Docker]() to run this tool.

## Running
Run:

	docker run -i -t --volume=/code/to/repository/:/code/ quay.io/keboola/component-generator

The path `/code/to/repository/` is expected to contain an empty [Git]() repository.

Options:
`--setup-only` -- only run setup of Travis deployment
`--update` -- use to update existing repository, will ask about each file before copying

Pass options in the command line like this:

	docker run -i -t --volume=/code/to/repository/:/code/ quay.io/keboola/component-generator --setup-only
