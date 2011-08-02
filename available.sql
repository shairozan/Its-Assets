SELECT		a.Id		as "value"		
,		a.os		as "label"
FROM 		Assets		a
,		Assignments	b
,		Employees	c
WHERE		a.Id = b.AssetId
AND	  	c.Id = b.EmployeeId
AND		b.EndDate=0
AND		a.AssetType in ('Desktop','Laptop')
AND		CONCAT(c.FirstName,', ',c.LastName) IN
			(
 				 'Server Room, A-1'
				,'Server Room, A-2'
				,'Server Room, A-3'
				,'Server Room, A-4'
				,'Server Room, B-1'
				,'Server Room, B-2'
				,'Server Room, B-3'
				,'Server Room, B-4'
			)

