SELECT status, COUNT(*) AS total
FROM events
GROUP BY status
ORDER BY status;

UPDATE events
SET status = 'open_registration'
WHERE status = 'published';

SELECT status, COUNT(*) AS total
FROM events
GROUP BY status
ORDER BY status;