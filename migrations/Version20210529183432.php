<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210529183432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchases (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, total DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase_product (purchase_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_C890CED4558FBEB9 (purchase_id), INDEX IDX_C890CED44584665A (product_id), PRIMARY KEY(purchase_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, quantity INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sale_product (sale_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_A654C63F4A7E4868 (sale_id), INDEX IDX_A654C63F4584665A (product_id), PRIMARY KEY(sale_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase_product ADD CONSTRAINT FK_C890CED4558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchases (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase_product ADD CONSTRAINT FK_C890CED44584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sale_product ADD CONSTRAINT FK_A654C63F4A7E4868 FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sale_product ADD CONSTRAINT FK_A654C63F4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_product DROP FOREIGN KEY FK_C890CED4558FBEB9');
        $this->addSql('ALTER TABLE sale_product DROP FOREIGN KEY FK_A654C63F4A7E4868');
        $this->addSql('DROP TABLE purchases');
        $this->addSql('DROP TABLE purchase_product');
        $this->addSql('DROP TABLE sales');
        $this->addSql('DROP TABLE sale_product');
    }
}
