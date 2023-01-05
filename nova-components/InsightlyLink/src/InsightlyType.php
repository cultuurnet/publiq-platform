<?php

namespace Publiq\InsightlyLink;

enum InsightlyType: string
{
    case Contact = 'Contact';
    case Opportunity = 'Opportunity';
    case Project = 'Project';
    case Organization = 'Organization';
}
