-- แสดงชื่อประเภทสินค้า ชื่อสินค้า และ ราคาสินค้า
Select c.CategoryName, p.ProductName, p.UnitPrice
From Products as p, Categories as c
Where p.CategoryID = c.CategoryID

-- แสดงชื่อประเภทสินค้า ชื่อสินค้า และ ราคาสินค้า
Select c.CategoryName, p.ProductName, p.UnitPrice
From Products as p, Categories as c
Where p.CategoryID = c.CategoryID
And CategoryName = 'seafood'

Select c.CategoryName, p.ProductName, p.UnitPrice
From Products as p join Categories as c
Where p.CategoryID = c.CategoryID
And CategoryName = 'seafood'

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
