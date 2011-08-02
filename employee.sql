SELECT	Id as "value"
,	CONCAT(c.FirstName,', ',c.LastName) as "lable"
from 	Employees c
where	(CONCAT(c.FirstName,', ',c.LastName) not like '%Server%'
and CONCAT(c.FirstName,', ',c.LastName) not like '%Admin%'
and CONCAT(c.FirstName,', ',c.LastName) not like '%Trade%')
Order by CONCAT(c.FirstName,', ',c.LastName) asc
