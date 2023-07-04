FROM manticoresearch/manticore

WORKDIR /app


COPY  ./manticore/wordforms.txt /var/lib/manticore/wordforms.txt
COPY  ./manticore/alternateforms.txt /var/lib/manticore/alternateforms.txt
COPY  ./manticore/exceptions.txt /var/lib/manticore/exceptions.txt
COPY  ./manticore/manticore.conf /etc/manticoresearch/manticore.conf
