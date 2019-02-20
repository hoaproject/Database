<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Database\Query;

/**
 * Trait \Hoa\Database\Query\EncloseIdentifier.
 *
 * Enclose identifier feature.
 */
trait EncloseIdentifier
{
    /**
     * State of the enclosing: Either true for enable the enclosing feature
     * or false for disable it.
     *
     * @var bool
     */
    protected $_enableEnclose = false;

    /**
     * Enclose opening symbol.
     *
     * @var string
     */
    protected $_openingSymbol = '"';

    /**
     * Enclose closing symbol.
     *
     * @var string
     */
    protected $_closingSymbol = '"';



    /**
     * Set enclose symbols.
     */
    public function setEncloseSymbol(string $openingSymbol, string $closingSymbol = null): Dml
    {
        $this->_openingSymbol = $openingSymbol;
        $this->_closingSymbol = $closingSymbol ?: $openingSymbol;

        return $this;
    }

    /**
     * Enable or disable enclosing identifiers.
     */
    public function enableEncloseIdentifier(bool $enable = true): bool
    {
        $old                  = $this->_enableEnclose;
        $this->_enableEnclose = $enable;

        return $old;
    }

    /**
     * Enclose identifiers with defined symbol.
     */
    protected function enclose($identifiers)
    {
        if (false === $this->_enableEnclose) {
            return $identifiers;
        }

        if (false === is_array($identifiers)) {
            return $this->_enclose($identifiers);
        }

        foreach ($identifiers as &$identifier) {
            $identifier = $this->_enclose($identifier);
        }

        return $identifiers;
    }

    /**
     * Enclose identifier with defined symbol.
     */
    protected function _enclose(string $identifier): string
    {
        if (0 === preg_match('#\s|\(#', $identifier)) {
            return $this->_openingSymbol . $identifier . $this->_closingSymbol;
        }

        return $identifier;
    }
}
