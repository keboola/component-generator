import csv

from keboola import docker


def run(datadir):
    cfg = docker.Config(datadir)
    parameters = cfg.get_parameters()
    print("Hello World!")
    print(parameters)
    in_file = datadir + '/in/tables/source.csv'
    out_file = datadir + '/out/tables/destination.csv'
    with open(in_file, mode='rt', encoding='utf-8') as in_file, \
            open(out_file, mode='wt', encoding='utf-8') as out_file:
        lazy_lines = (line.replace('\0', '') for line in in_file)
        reader = csv.DictReader(lazy_lines, dialect='kbc')
        writer = csv.DictWriter(out_file, dialect='kbc',
                                fieldnames=reader.fieldnames)
        writer.writeheader()
        for row in reader:
            writer.writerow({'id': int(row['id']) * 42,
                             'sound': row['sound'] + 'ping'})
