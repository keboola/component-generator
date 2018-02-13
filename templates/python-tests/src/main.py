import my_component
import os
import sys
import traceback

try:
    datadir = os.environ.get('KBC_DATADIR') or '/data/'
    my_component.run(datadir)
except ValueError as err:
    print(err, file=sys.stderr)
    sys.exit(1)
except Exception as err:
    print(err, file=sys.stderr)
    traceback.print_exc(file=sys.stderr)
    sys.exit(2)
