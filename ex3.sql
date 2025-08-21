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



--จงแสดงหมายเลขใบสั่งซื้อ, ชื่อบริษัทลูกค้า,สถานที่ส่งของ, และพนักงานผู้ดูแล
SELECT O.OrderID เลขใบสั่งซื้อ, C.CompanyName ลูกค้า,
E.FirstName พนักงาน, O.ShipAddress ส่งไปที่
FROM Orders O, Customers C, Employees E
WHERE O.CustomerID=C.CustomerID
AND O.EmployeeID=E.EmployeeID
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               
--จงแสดงหมายเลขใบสั่งซื้อ, ชื่อบริษัทลูกค้า,สถานที่ส่งของ, และพนักงานผู้ดูแล
SELECT O.OrderID เลขใบสั่งซื้อ, C.CompanyName ลูกค้า,
E.FirstName พนักงาน, O.ShipAddress ส่งไปที่
FROM Orders O
join Customers C on O.CustomerID=C.CustomerID
join Employees E on O.EmployeeID=E.EmployeeID

--การเชื่อมโยงตารางใช้ร่วมกับ AGGREGATE FUNCTION
select e.EmployeeID, FirstName , count(*) as [จ านวน order]
, sum(freight) as [Sum of Freight]
from Employees e join Orders o on e.EmployeeID = o.EmployeeID
where year(orderdate) = 1998
group by e.EmployeeID, FirstName

-- ต้องการชื่อบริษัทขนส่ง และจำนวนใบสั่งซื้อที่เกี่ยวข้อง
select s.CompanyName, COUNT(*) จำนวนorders
from Shippers s JOIN orders o on s.ShipperID = o.ShipVia
group BY s.CompanyName
order by 2 DESC


-- ต้องการรหัสสินค้า ชื่อสินค้า และจำนวนทั้งหมดที่ขายได้
SELECT p.ProductID, p.ProductName, sum(Quantity) จำนวนที่ขายได้
from products p join [Order Details] od on p.ProductID = od.ProductID
GROUP by p.ProductID, p.ProductName

-- ต้องการรหัสสินค้า ชื่อสินค้า ที่ nancy ขายได้ทั้งหมด เรียงตามลำดับสินค้า
SELECT distinct p.ProductID, p.ProductName
FROM Employees e join orders o on e.EmployeeID = o.EmployeeID
                 JOIN [Order Details] Od on o.OrderID = od.OrderID
                 JOIN Products p on p.ProductID = od.ProductID
where e.FirstName = 'Nancy'
order by ProductID
