<?php

namespace PDGA\DataObjects\Models\Test;

use DateTime;
use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Attributes\ManyToOne;
use PDGA\DataObjects\Attributes\OneToMany;
use PDGA\DataObjects\Attributes\Table;
use PDGA\DataObjects\Converters\YesNoConverter;
use PDGA\DataObjects\Converters\DateTimeConverter;
use PDGA\DataObjects\Interfaces\ISensitiveDataObject;

/**
 * Placeholder class.
 */
#[Table]
class SensitiveTestDataObject implements ISensitiveDataObject
{
    public const SENSITIVE_PROPERTIES = [
        'email',
        'birthDate',
        'privacy',
    ];

    #[Column(
        name: 'PDGANum',
        sqlDataType: 'int',
    )]
    public ?int $pdgaNumber;

    #[Column(
        name: 'FirstName',
        sqlDataType: 'varchar',
    )]
    public ?string $firstName;

    #[Column(
        name: 'LastName',
        sqlDataType: 'varchar',
    )]
    public ?string $lastName;

    #[Column(
        name: 'Email',
        sqlDataType: 'varchar',
    )]
    public ?string $email;

    #[Column(
        name: 'Privacy',
        sqlDataType: 'enum',
        converter: new YesNoConverter(),
    )]
    public ?bool $privacy;

    #[Column(
        name: 'BirthDate',
        sqlDataType: 'date',
        converter: new DateTimeConverter(),
    )]
    public ?DateTime $birthDate;

    /**
     * Used to test properties without a Column attribute.
     *
     * @var bool
     */
    public bool $testProperty = false;

    // Used to test ManyToOne relations.
    #[ManyToOne(
        SensitiveTestDataObject::class,
        'FakeHasOneRelation',
    )]
    public SensitiveTestDataObject $fakeHasOneRelation;

    // Used to test nullable ManyToOne relations.
    #[ManyToOne(
        SensitiveTestDataObject::class,
        'NullableFakeHasOneRelation',
    )]
    public ?SensitiveTestDataObject $nullableFakeHasOneRelation;

    // Used to test OneToMany relations.
    #[OneToMany(
        SensitiveTestDataObject::class,
        'FakeHasManyRelation',
    )]
    public array $fakeHasManyRelation;

    public function cleanseSensitiveFields(): void
    {
        // Unsets the values for the sensitive properties
        if (isset($this->privacy) && $this->privacy) {
            foreach (self::SENSITIVE_PROPERTIES as $property) {
                unset($this->$property);
            }
        }
    }
}
