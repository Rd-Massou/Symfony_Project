<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210530152708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE purchase_product');
        $this->addSql('DROP TABLE sale_product');
        $this->addSql('ALTER TABLE purchases ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FE4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('CREATE INDEX IDX_AA6431FE4584665A ON purchases (product_id)');
        $this->addSql('ALTER TABLE sales ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE sales ADD CONSTRAINT FK_6B8170444584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('CREATE INDEX IDX_6B8170444584665A ON sales (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchase_product (purchase_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_C890CED4558FBEB9 (purchase_id), INDEX IDX_C890CED44584665A (product_id), PRIMARY KEY(purchase_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sale_product (sale_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_A654C63F4A7E4868 (sale_id), INDEX IDX_A654C63F4584665A (product_id), PRIMARY KEY(sale_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE purchase_product ADD CONSTRAINT FK_C890CED44584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase_product ADD CONSTRAINT FK_C890CED4558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchases (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sale_product ADD CONSTRAINT FK_A654C63F4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sale_product ADD CONSTRAINT FK_A654C63F4A7E4868 FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchases DROP FOREIGN KEY FK_AA6431FE4584665A');
        $this->addSql('DROP INDEX IDX_AA6431FE4584665A ON purchases');
        $this->addSql('ALTER TABLE purchases DROP product_id');
        $this->addSql('ALTER TABLE sales DROP FOREIGN KEY FK_6B8170444584665A');
        $this->addSql('DROP INDEX IDX_6B8170444584665A ON sales');
        $this->addSql('ALTER TABLE sales DROP product_id');
    }
}
