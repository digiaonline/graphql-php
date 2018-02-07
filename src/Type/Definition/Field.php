<?php

namespace Digia\GraphQL\Type\Definition;

class Field
{

    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use ArgumentsTrait;
    use IsDeprecatedTrait;
    use DeprecationReasonTrait;
    use ResolveTrait;
    use ConfigTrait;
}
