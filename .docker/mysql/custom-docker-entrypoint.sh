#!/usr/bin/env bash

# Include original entrypoint script
source /usr/local/bin/docker-entrypoint.sh

create_database() {
    mysql_note "Creating database $1"
    docker_process_sql --database=mysql <<<"CREATE DATABASE IF NOT EXISTS \`$1\` ;"
}

# Override docker_process_init_files function to create databases based on dump file names, and use them when importing data
# See the original in https://github.com/docker-library/mysql/blob/master/8.0/docker-entrypoint.sh
docker_process_init_files() {
	# mysql here for backwards compatibility "${mysql[@]}"
	mysql=( docker_process_sql )

	echo
	local f
	local filename
	local db

	for f; do
        filename=$(basename "$f")
        db=${filename%%.*}

		case "$f" in
			*.sh)
				# https://github.com/docker-library/postgres/issues/450#issuecomment-393167936
				# https://github.com/docker-library/postgres/pull/452
				if [ -x "$f" ]; then
					mysql_note "$0: running $f"
					"$f"
				else
					mysql_note "$0: sourcing $f"
					. "$f"
				fi
				;;
			*.sql)     mysql_note "$0: running $f"; create_database $db; docker_process_sql --database="$db" < "$f"; echo ;;
			*.sql.bz2) mysql_note "$0: running $f"; create_database $db; bunzip2 -c "$f" | docker_process_sql --database="$db"; echo ;;
			*.sql.gz)  mysql_note "$0: running $f"; create_database $db; gunzip -c "$f" | docker_process_sql --database="$db"; echo ;;
			*.sql.xz)  mysql_note "$0: running $f"; create_database $db; xzcat "$f" | docker_process_sql --database="$db"; echo ;;
			*.sql.zst) mysql_note "$0: running $f"; create_database $db; zstd -dc "$f" | docker_process_sql --database="$db"; echo ;;
			*)         mysql_warn "$0: ignoring $f" ;;
		esac
		echo
	done
}

# Run _main from original entrypoint script
_main "$@"
