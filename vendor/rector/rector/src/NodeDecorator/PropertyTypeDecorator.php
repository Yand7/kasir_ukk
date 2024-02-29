<?php

declare (strict_types=1);
namespace Rector\Core\NodeDecorator;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class PropertyTypeDecorator
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function decorate(\PhpParser\Node\Stmt\Property $property, ?\PHPStan\Type\Type $type) : void
    {
        if ($type === null) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES)) {
            $phpParserType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
            if ($phpParserType !== null) {
                $property->type = $phpParserType;
                if ($type instanceof \PHPStan\Type\Generic\GenericObjectType) {
                    $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
                }
                return;
            }
        }
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
    }
}