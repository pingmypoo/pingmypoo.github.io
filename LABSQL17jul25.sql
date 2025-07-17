---ต้องการข้อมูลพนักงานทั้งหมด
select * from Employees
---รหัสพลักงานชื่อนามสกุล พนักงานทุกคน
select EmployeeID,FirstName,LastName from Employees

select * from Employees WHERE City = 'london'

select EmployeeID, FirstName, LastName FROM Employees WHERE City = 'london'

select City, Country From Customers

select DISTINCT City, Country From Customers 