How stats work.

All stats are calculated from the transitions table and config information, they are to be calculated hourly and stored in a database table. These can then be aggregated into period like 3 hours, 6hours , 12hours 24,hours , 1 week etc using the date in the where condition.

Worker (Processor)

1. Min time taken by worker.
2. Max time taken by a worker.
3. Mean time taken by worker.
4. Mean of jobs processed by worker (throughput).
5. Maxium jobs could been allocated (maximum throughput) from config.
6. Mean utilization of worker  throughput/maxium throughput.

Queue
1. Number of queued jobs ie waiting non locked.
2. Number of locked jobs ie processing.
3. Number of failed jobs.
4. Number of jobs marked error needing retry.
5. Number of jobs completed.

6. Mean Service time, avg time taken for a job to be completed once added to queue.
7. Min time taken to complete a job for this queue.
8. Max time taken to complete a job in the queue.


