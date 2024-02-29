ARG image
FROM $image
COPY ./mysql/custom-docker-entrypoint.sh /custom-docker-entrypoint.sh
ENTRYPOINT ["/custom-docker-entrypoint.sh"]
EXPOSE 3306 33060
CMD ["mysqld"]
