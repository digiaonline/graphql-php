<?php

namespace Digia\GraphQL\Type\Definition;

class InterfaceType implements AbstractTypeInterface, CompositeTypeInterface, NamedTypeInterface, OutputTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use ConfigTrait;
}
