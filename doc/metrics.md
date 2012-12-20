# Metrics

The metrics are recorded for job and workers,

## Workers
1. Maximum executing time of a worker in the hour.
2. Minimum executing time of a worker in the hour.
3. Mean executing time of a worker in the hour.
4. Mean Throughput of wokers in the hour, the number of jobs processed by worker.
5. Maxium Throughput, from config, maximum number of jobs that a worker can process.
6. Worker Utilization (Mean Throughput / Maximum Throughput).

## Jobs
1. Number of jobs added in the last hour.
2. Number of jobs started in the last hour.
3. Number of jobs failed in the last hour.
4. Number of jobs finished in the last hour.
5. Number of jobs in error in the last hour.
6. Mean service time, ie time from when job transitioned to added to finished / failed.
7. Minimum Service Time.
8. Maximum Service Time.

### The most important metric?
The mean service time and worker utilization should be watched closley. If the service time is greater than a base time jobs are not cleared fast enough. If worker utilization is high scripts are processing their maximum.

### Increase Capacity?
There are two options run workers more frequently or increase the maximum throughput thus lowering utilization and increasing capacity for busy times. 

