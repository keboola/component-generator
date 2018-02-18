#' Example custom KBC application in R
#' @import methods
#' @import keboola.r.docker.application
#' @export CustomApplicationExample
#' @exportClass CustomApplicationExample
CustomApplicationExample <- setRefClass(
    'CustomApplicationExample',
    contains = c("DockerApplication"),
    fields = list(),
    methods = list(
        run = function() {
            "Main application run function."

            # intialize application
            readConfig()

            # read input
            data <- read.csv(file = file.path(dataDir, "in/tables/source.csv"));

            # do something clever
            data['double_number'] <- data['number'] * getParameters()$multiplier

            # write output
            write.csv(data, file = file.path(dataDir, "out/tables/result.csv"), row.names = FALSE)
        }
    )
)
