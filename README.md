# Keboola Component Skeleton Generator
This tool generates a skeleton for a new [Keboola Connection](https://connection.keboola.com/) component. 
See the [Development Guide](https://developers.keboola.com/extend/component/) for more details.

This tool is working on a checked out **GitHub repository** and sets a Travis deployment. 
See the documentation for working with 
[Bitbucket](https://developers.keboola.com/extend/component/deployment/#bitbucket-integration) or 
[Gitlab](https://developers.keboola.com/extend/component/deployment/#gitlab-integration) repository.
You need [Docker](https://www.docker.com/) to run this tool.

If you don't need setting up Travis integration, you may simply copy the files from templates directories.
In that case don't forget to run `git update-index --chmod=+x deploy.sh` to make the deployment scripts executable.

## Running
Before you run this tool, you should have created a **new component and** a **new service account**
in the [Developer Portal](https://components.keboola.com/). See our 
[Component Tutorial](https://developers.keboola.com/extend/component/tutorial/) for more detailed instructions.
Before you start, you should have:

- vendor ID and component ID
- service account username and password 
 
Run:

	docker run --rm -i -t --volume=/path/to/repository/:/code/ quay.io/keboola/component-generator

The path `/path/to/repository/` is expected to contain an empty [GitHub](https://github.com/) repository.

Options:
`--setup-only` -- only run setup of Travis deployment
`--update` -- use to update existing repository, will ask about each file before copying

Pass options in the command line like this:

	docker run --rm -i -t --volume=/path/to/repository/:/code/ quay.io/keboola/component-generator --setup-only

Setup of travis deployment does the following:

- enable building of the repository
- build only if .travis.yml is present
- set `KBC_DEVELOPERPORTAL_VENDOR` variable
- set `KBC_DEVELOPERPORTAL_APP` variable
- set `KBC_DEVELOPERPORTAL_USERNAME` variable
- set `KBC_DEVELOPERPORTAL_PASSWORD` variable

## Development
To run the component generator locally on a local repository, you need to map two volumes, e.g.:

```
docker build . -t component-generator-dev
docker run --rm -it -v /path/to/genrator/:/init-code/ -v /path/to/repository/:/code/ --entrypoint=/bin/bash component-generator-dev
```

Then run the generator with `php /init-code/application.php`. The generator assumes that its code is located 
in the `/init-code/` directory and the repository to be initialized is located in the `/code/` directory.
