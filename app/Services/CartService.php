<?php
namespace App\Services;

use App\Core\Database;

final class CartService {
    private static function cartId(int $customerId): int {
        $pdo=Database::pdo();$statement=$pdo->prepare('SELECT id FROM carts WHERE customer_id=? LIMIT 1');$statement->execute([$customerId]);$cart=$statement->fetch();
        if($cart)return (int)$cart['id'];$pdo->prepare('INSERT INTO carts(customer_id) VALUES(?)')->execute([$customerId]);return (int)$pdo->lastInsertId();
    }
    public static function mergeIntoCustomer(int $customerId): void {$pdo=Database::pdo();$cartId=self::cartId($customerId);$guest=array_map('intval',array_keys($_SESSION['cart']??[]));$insert=$pdo->prepare('INSERT IGNORE INTO cart_items(cart_id,book_id) VALUES(?,?)');foreach($guest as $bookId)$insert->execute([$cartId,$bookId]);$statement=$pdo->prepare('SELECT book_id FROM cart_items WHERE cart_id=?');$statement->execute([$cartId]);$_SESSION['cart']=array_fill_keys(array_map('intval',array_column($statement->fetchAll(),'book_id')),1);}
    public static function add(int $customerId,int $bookId): void {$pdo=Database::pdo();$pdo->prepare('INSERT IGNORE INTO cart_items(cart_id,book_id) VALUES(?,?)')->execute([self::cartId($customerId),$bookId]);}
    public static function remove(int $customerId,int $bookId): void {$pdo=Database::pdo();$pdo->prepare('DELETE ci FROM cart_items ci JOIN carts c ON c.id=ci.cart_id WHERE c.customer_id=? AND ci.book_id=?')->execute([$customerId,$bookId]);}
    public static function clear(int $customerId): void {$pdo=Database::pdo();$pdo->prepare('DELETE ci FROM cart_items ci JOIN carts c ON c.id=ci.cart_id WHERE c.customer_id=?')->execute([$customerId]);}
}
