-- แสดงชื่อประเภทสินค้า ชื่อสินค้า และ ราคาสินค้า
Select c.CategoryName, p.ProductName, p.UnitPrice
From Products as p, Categories as c
Where p.CategoryID = c.CategoryID

-- แสดงชื่อประเภทสินค้า ชื่อสินค้า และ ราคาสินค้า
Select c.CategoryName, p.ProductName, p.UnitPrice
From Products as p, Categories as c
Where p.CategoryID = c.CategoryID
And CategoryName = 'seafood'

SELECT c.CategoryName, p.ProductName, p.UnitPrice
FROM Products AS p 
JOIN Categories AS c ON p.CategoryID = c.CategoryID
WHERE c.CategoryName = 'seafood'

-- จงแสดงข้อมูลหมายเลขใบสั่งซื้อและชื่อบริษัทขนส่งสินค้า
SELECT CompanyName, OrderID
FROM Orders, Shippers
WHERE Shippers.ShipperID = Orders.Shipvia

SELECT CompanyName, OrderID
FROM Orders JOIN Shippers
ON Shippers.ShipperID = Orders.Shipvia

-- จงแสดงข้อมูลหมายเลขใบสั่งซื้อและชื่อบริษัทขนส่งสินค้าของใบสั่งซื้อหมายเลข 10275
SELECT CompanyName, OrderID
FROM Orders, Shippers
WHERE Shippers.ShipperID = Orders.Shipvia
AND OrderID = 10275

SELECT CompanyName, OrderID
FROM Orders JOIN Shippers
ON Shippers.ShipperID=Orders.Shipvia
WHERE OrderID=10275

--ต้องการรหัสสินค้าชอสินค้า บริษัทผู้จำหน่าย ประเทศ
SELECT p.ProductID, p.ProductName, s.CompanyName, s.Country
FROM products p join Suppliers s on p.SupplierID = s.SupplierID
WHERE Country in ('usa' , 'uk')
--ต้องการรหัสพนักงาน ชื่อพนักงาน รหัสใบสั่งซื้อที่เกี่ยวข้อง เรียงตามลำดับรหัสพนักงาน
SELECT e.EmployeeID, FirstName, o.OrderID
from Employees e JOIN orders o ON e.EmployeeID = o.EmployeeID
ORDER BY EmployeeID
--ต้องการรหัสสินค้า ชื่อสินค้า เมือง และประเทศของบริษัทผูั้จำหน่าย

SELECT O.OrderID เลขใบสั่งซื้อ, C.CompanyName ลูกค้า,
E.FirstName พนักงาน, O.ShipAddress ส่งไปที่
FROM Orders O, Customers C, Employees E
WHERE O.CustomerID=C.CustomerID
AND O.EmployeeID=E.EmployeeID
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               

SELECT O.OrderID เลขใบสั่งซื้อ, C.CompanyName ลูกค้า,
E.FirstName พนักงาน, O.ShipAddress ส่งไปที่
FROM Orders O
join Customers C on O.CustomerID=C.CustomerID
join Employees E on O.EmployeeID=E.EmployeeID
